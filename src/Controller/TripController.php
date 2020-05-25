<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\Booking;
use App\Form\TripType;
use App\Repository\BookingRepository;
use App\Repository\CityRepository;
use App\Repository\TripRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/trip")
 */
class TripController extends AbstractController
{

    private $security;
    private $om;
    private $repository;

    public function __construct(TripRepository $repository, Security $security, ObjectManager $om){
        $this->repository = $repository;
        $this->security = $security;
        $this->om = $om;
    }

    /**
     * @Route("/home", name="home")
     * @Route("/", name="trip")
     */
    public function home(): Response
    {
        return $this->render('trip/home.html.twig', [
            'breadcrum' => "home",
        ]);
    }

    /**
     * Display the page of search
     * @Route("/search", name="search")
     */
    public function search(Request $request): Response
    {
        $cityNameFrom = $request->get("from");
        $cityNameTo= $request->get("to");
        return $this->render('trip/search.html.twig', [
            'breadcrum' => "home > trip > search",
            'from_name' => $cityNameFrom,
            'to_name' => $cityNameTo,
        ]);
    }

    /**
     * Find the next trips to start
     * @Route("/recent", name="trip_recent")
     */
    public function recentTrips(Request $request, PaginatorInterface $paginator): Response
    {
        $trips = $paginator->paginate(
            $this->repository->mostRecentActiveTripQuery(),
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('trip/listing.html.twig', [
            'breadcrum' => "home > trip > recent",
            'trips' => $trips,
            'trip_label' => 'Most recent trips published',
        ]);
    }

    /**
     * Create a new trip
     * @Route("/create", name="trip_create", methods={"GET|POST"})
     */
    public function create(Request $request): Response
    {
        $trip = new Trip();
        $trip->setDriver($this->getUser());
        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        //Ensure that the user is creating for him
        if ($form->isSubmitted() && $form->isValid()){
            $this->om->persist($trip);
            $this->om->flush();
            $this->addFlash('success', 'The trip has been created');
            return $this->redirectToRoute('trip_view', ['id'=>$trip->getId()]);
        }
        return $this->render('trip/new.html.twig', [
            'breadcrum' => "home > trip > create",
            'trip' => $trip,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Find a trip by date and destination
     * @Route("/find", name="trip_find", methods={"GET"})
     */
    public function find(Request $request, PaginatorInterface $paginator, CityRepository $cityRepository): Response
    {
        //gets informations about the research
        $cityIdFrom = $request->get("from");
        $cityIdTo = $request->get("to");
        $tripTime = $request->get("at");
        //checks the values
        if ($cityIdFrom == null || $cityIdTo == null || $tripTime == null){
            return $this->redirectToRoute('home', [], 301);
        }
        //Define the date in the right format
        $datetime = \DateTime::createFromFormat('Y-m-d H:i', $tripTime);
        //Searchs the city which corresponds to the values
        $cityFrom = $cityRepository->find($cityIdFrom);
        $cityTo = $cityRepository->find($cityIdTo);

        //Get the nearest city of the departure and the arrival
        $nearestCitiesFrom = $cityRepository->getNearestCities($cityFrom);
        $nearestCitiesTo = $cityRepository->getNearestCities($cityTo);
        $trips = $this->repository->findTripsFromArray($cityFrom, $nearestCitiesFrom, $cityTo, $nearestCitiesTo, $tripTime);
        $bestTrip = $trips["bestTrip"];
        $goodTrips = $paginator->paginate(
            $trips["goodTrips"],
            $request->query->getInt('page', 1),
            5
        );

        //returns the list of the results
        return $this->render('trip/listing.html.twig',
            ['best_trip' => $bestTrip,
             'trips' => $goodTrips,
             'breadcrum' => "home > trip > search > result",
            ]
        );
    }

    /**
     * Return the detail's trip
     * @Route("/{id}", name="trip_view", methods={"GET"})
     */
    public function view(Trip $trip, BookingRepository $bookingRepository): Response
    {
        $availableSeats = $bookingRepository->getAvailableSeatsForTrip($trip);
        $user = $this->getUser();
        $alreadyBookedSeats = 0;
        if ($user){
            $alreadyBooked = $bookingRepository->findBy(
                [
                    'user' => $user->getId(),
                    'trip' => $trip->getId(),
                ]
            );
            if ($alreadyBooked){
                $alreadyBookedSeats = $alreadyBooked[0]->getSeats();
            }
        }


        return $this->render('trip/view.html.twig', [
            'breadcrum' => "home > trip > ".$trip->getId(),
            'trip' => $trip,
            'seats' => $availableSeats,
            'already_booked_seats' => $alreadyBookedSeats,
        ]);
    }

    /**
     * Allows a logged user to edit one of his trip
     * @Route("/{id}", name="trip_edit", methods={"POST"})
     */
    public function edit(Trip $trip, Request $request): Response
    {
        //checks if the driver's trip is the logged user
        if($trip->getDriver() == $this->getUser()){
            $form = $this->createForm(TripType::class, $trip);
            $form->handleRequest($request);

            //Check the values
            if ($form->isSubmitted() && $form->isValid()){
                //Send the values
                $this->om->flush();
            }else{
                return $this->render('trip/edit.html.twig', [
                    'breadcrum' => "home > trip > ".$trip->getId(),
                    'form' => $form->createView(),
                ]);
            }
        }else{
            $this->addFlash('warning', "You can not edit someone else trip");
        }
        return $this->redirectToRoute('trip_view', ['id'=>$trip->getId()]);
    }

    /**
     * Allows a logged user to delete one of his trip
     * @Route("/{id}", name="trip_delete", methods={"DELETE"})
     */
    public function delete(Trip $trip, Request $request): Response
    {
        if($trip->getDriver() == $this->getUser()){
            if ($this->isCsrfTokenValid('delete'.$trip->getId(), $request->get('_token'))){
                $this->om->remove($trip);
                $this->om->flush();
                $this->addFlash('success', "The trip has been deleted");
                return $this->redirectToRoute('account_history');
            }
        }else{
            $this->addFlash('warning', "You can not delete someone else trip");
        }
        return $this->redirectToRoute('trip_view', ['id'=>$trip->getId()]);
    }
}
