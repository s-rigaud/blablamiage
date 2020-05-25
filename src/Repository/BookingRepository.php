<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\trip;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }


    /**
     * Find all the booking of a user
     * @param id : the user whose reservations are being search
     */
    public function findBookingForUser(int $id){
        return $this->findBy(['user' => $id], ['created' => "DESC"]);
    }

    public function getAvailableSeatsForTrip(Trip $trip){
        $takenSeats = $this->createQueryBuilder('b')
            ->select('SUM(b.seats)')
            ->Where('b.trip = :tripId')
            ->setParameter('tripId', $trip->getId())
            ->getQuery()
            ->getSingleResult();
        if ($takenSeats === array(null)){
            $takenSeats = [0];
        }
        return $trip->getMaxSeats() - \array_pop($takenSeats);
    }
}