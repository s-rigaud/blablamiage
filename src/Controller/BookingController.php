<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use App\Repository\TripRepository;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/booking")
*/
class BookingController extends AbstractController
{
    private $om;
    private $security;

    public function __construct(Security $security, ObjectManager $om){
        $this->om = $om;
        $this->security = $security;
    }
    /**
     * Redirection to the user's bookings history
     * @Route("/", name="booking")
     */
    public function index()
    {
        return $this->redirectToRoute('account_history', [
            '' => ""
        ]);
    }

    /**
     * Allows a logged user to book a trip
     * @Route("/create", name="booking_create", methods={"POST"})
     */
    public function book(Request $request, BookingRepository $repository, TripRepository $tripRepository): Response
    {
        $user = $this->getUser();
        $trip = $tripRepository->find($request->get('trip_id'));
        # No trip means 404 which is ok in production

        if($trip->getDriver()->getId() != $user->getId()){
            $seat_number = $request->get('seats');
            $availableSeats = $repository->getAvailableSeatsForTrip($trip);

            # If trip as available seats
            if ($seat_number && $seat_number <= $availableSeats){
                $booking = $repository->findOneBy(['user' => $user->getId(), 'trip' => $trip->getId()]);
                if ($booking){
                    $booking->setSeats($booking->getSeats() + $seat_number);
                    $this->addFlash('success', "Your previous booking seat number have been incread by ".$seat_number);
                }else{
                    $booking = new Booking();
                    $booking->setSeats($seat_number);
                    $booking->setUser($user);
                    $booking->setTrip($trip);

                    //save the booking
                    $this->om->persist($booking);
                    $this->om->flush();
                    $this->addFlash('success', "Your booking have been created, you will travel to ".$trip->getToC()->getName());
                }
                $this->om->persist($booking);
                $this->om->flush();
                return $this->redirectToRoute('booking_view', [
                    'id' => $booking->getId()]
                );
            }
            //alerts the user that he's the driver's trip
            $this->addFlash('warning', "You tried to book too many seats");
            return $this->redirectToRoute('trip_view', [
                'id' => $trip->getId(),
            ]);
        }
        //alerts the user that he's the driver's trip
        $this->addFlash('warning', "You can't book your own trip");
        return $this->redirectToRoute('trip_view', [
            'id' => $trip->getId(),
        ]);
    }

    /**
     * Display the booking of the logged user
     * @Route("/{id}", name="booking_view", methods={"GET|POST"})
     */
    public function view(Booking $booking)
    {
        //check if the logged user is the one who makes the booking
        if ($this->getUser() == $booking->getUser()){
            //display the view of the booking
            return $this->render('booking/view.html.twig', [
                'booking' => $booking,
                'breadcrum' => "home > booking > ".$booking->getId(),
            ]);
        }
        //display on the top of the page that the user can't update someone's else booking
        $this->addFlash('warning', "You tried to edit another person's booking");
        return $this->redirectToRoute('trip_view', [
            'id' => $booking->getTrip()->getId(),
        ]);
    }

    /**
     * Method for delete one booking
     * @Route("/{id}", name="booking_delete", methods={"DELETE"})
     */
    public function delete(Booking $booking, ObjectManager $objectManager, Request $request): Response
    {
        //check if the logged user is the one who makes the booking
        if($this->getUser() == $booking->getUser()){
            //check  if the CSRF token is valid
            if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->get('_token'))){
                //delete the booking and redirect the user to the view with all his bookings
                $objectManager->remove($booking);
                $objectManager->flush();
                return $this->redirectToRoute('account_history');
            }
            # BAD CSRF Token
            //display the error on the top of the page
            $this->addFlash('error', 'Something went wrong');
            return $this->redirectToRoute('booking_view', [
                'id' => $booking->getId(),
            ]);
        }
        //display on the top of the page that the user is unauthorised to delete someone's else booking
        $this->addFlash('error', 'You can not delete someone else booking');
        return $this->redirectToRoute('booking_view', [
            'id'=>$booking->getId(),
        ]);
    }
}
