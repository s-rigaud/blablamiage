<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Entity\City;
use App\Repository\BookingRepository;

use Doctrine\ORM\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Trip|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trip|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trip[]    findAll()
 * @method Trip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    /**
     * Search the recent current trips
     */
    public function archivedTripsForUserQuery(int $userId, ?string $isArchived): Query
    {
        $request = $this->createQueryBuilder('t')
                        ->where("t.driver = ?2")
                        ->setParameter(2, $userId);

        if ($isArchived){
            $request->andWhere("t.end < ?1")
                    ->setParameter(1, new \Datetime());
        }else{
            $request->andWhere("t.end > ?1")
                    ->setParameter(1, new \Datetime());
        }
        return $request->orderBy('t.start', 'DESC')->getQuery();
    }

    /**
     * Search the recent current trips
     */
    public function mostRecentActiveTripQuery(): Query
    {
        return $this->createQueryBuilder('t')
            ->where("t.end > ?1")
            ->setParameter(1, new \Datetime())
            ->orderBy('t.created', 'DESC')
            ->getQuery();
    }

    /**
     * Find all the trips between two cities whose starts after a date
     * @param cityFrom : the departure's city
     * @param cityTo : the arrival's city
     * @param startingDateTime : the start date
     */
    private function findTripByCities(City $cityFrom, City $cityTo, string $startingDatetime)
    {
        //create and execute the querry who search all the trips between the two cities after a date given
        return $this->createQueryBuilder("t")
            ->Where("t.fromC = :cityIdFrom")
            ->andWhere("t.toC = :cityIdTo")
            ->andWhere("t.start > :startingDatetime")
            ->setParameter("cityIdFrom", $cityFrom->getId())
            ->setParameter("cityIdTo", $cityTo->getId())
            ->setParameter("startingDatetime", $startingDatetime)
            ->orderBy("t.start", "ASC")
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate the distance between two cities
     * @param lat1 : latitude of first city
     * @param lng1 : latitude of second city
     * @param lat2 : longitude of first city
     * @param lng2 : longitude of second city
     */
    // links of the mathematical formula :
    # https://phpsources.net/code/php/maths/459_distance-en-metre-entre-deux-points-avec-coordonnees-gps
    private function getDistanceInM(string $lat1, string $lng1, string $lat2, string $lng2) {
        $earth_radius = 6378137;   // Terre = sphère de 6378km de rayon
        $rlo1 = deg2rad($lng1);
        $rla1 = deg2rad($lat1);
        $rlo2 = deg2rad($lng2);
        $rla2 = deg2rad($lat2);
        $dlo = ($rlo2 - $rlo1) / 2;
        $dla = ($rla2 - $rla1) / 2;
        $a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
        $d = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earth_radius * $d, 2);
      }

    /**
     * return the distance between two cities
     * @param cityFrom : the departure's city of a trip
     * @param cityTo : the arrival's city of a trip
     */
    private function getDistCities(City $cityFrom, City $cityTo){
        return $this->getDistanceInM($cityFrom->getLatitude(), $cityFrom->getLongitude(), $cityTo->getLatitude(), $cityTo->getLongitude());
    }

    public function findTripsFromArray(City $originalCityFrom, array $nearestCitiesFrom, City $originalCityTo, array $nearestCitiesTo, string $startingDatetime){
        $bestTrip = null;
        $goodTrips = [];
        $distance = +100000; #In meters
        for($i=0; $i<sizeof($nearestCitiesFrom); $i++){
            for ($j=0; $j<sizeof($nearestCitiesTo); $j++){
                $temp_trip = $this->findTripByCities($nearestCitiesFrom[$i], $nearestCitiesTo[$j], $startingDatetime);
                if (! is_null($temp_trip)){
                    $distFrom = $this->getDistCities($originalCityFrom, $nearestCitiesFrom[$i]);
                    $distTo = $this->getDistCities($originalCityTo, $nearestCitiesTo[$j]);
                    foreach ($temp_trip as $tp){
                        \array_push($goodTrips, $tp);
                        # could be optimised (compute only for first trip between cities)
                        if(($distTo + $distFrom) < $distance){
                            $bestTrip = $tp;
                            $distance = $distTo + $distFrom;
                        }
                    }
                }
            }
        }
        return ["goodTrips" => $goodTrips, "bestTrip" => $bestTrip];
    }
}
