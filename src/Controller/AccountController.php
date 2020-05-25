<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BookingRepository;
use App\Repository\TripRepository;

use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountController extends AbstractController
{

    private $om;
    private $security;

    public function __construct(Security $security, ObjectManager $om)
    {
        $this->om = $om;
        $this->security = $security;
    }

    /**
    * Define view of account's details with the breadcrumb
    * @Route("/account", name="account")
    */
    public function accountMenu(): Response
    {
        return $this->render('account/main.html.twig', [
            'breadcrum' => "home > account",
        ]);
    }

    /**
    * @Route("/account/history", name="account_history")
    */
    public function history(Request $request, PaginatorInterface $paginator, BookingRepository $bookingRepository, TripRepository $tripRepository): Response
    {
        //get all the values to display on the page
        $userId = $this->getUser()->getId();

        $isArchived = $request->get('archived');
        $trips = $tripRepository->archivedTripsForUserQuery($userId, $isArchived);

        $lastTrips = $paginator->paginate(
            $trips,
            $request->query->getInt('page', 1),
            2
        );
        $lastBookings = $paginator->paginate(
            $bookingRepository->findBookingForUser($userId, 5, ['created' => "DESC"]),
            $request->query->getInt('pageB', 1),
            2,
            ['pageParameterName' => 'pageB']
        );

        //Return view of account's history with the breadcrumb
        return $this->render('account/history.html.twig', [
            'breadcrum' => "home > account > history",
            'trips' => $lastTrips,
            'bookings' => $lastBookings,
            'archived' => $isArchived,
        ]);
    }

    /**
    * @Route("/language/{_locale}", name="account_locale")
    */
    public function locale(Request $request, $_locale): Response
    {
        $user = $this->getUser();
        if ($user){
            $user->setLocale($_locale);
            $this->om->persist($user);
            $this->om->flush();
        }
        return $this->redirect($request->headers->get('referer'));
    }

}
