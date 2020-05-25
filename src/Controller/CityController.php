<?php

namespace App\Controller;

use App\Entity\City;
use App\Repository\CityRepository;


use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/city")
 */
class CityController extends AbstractController
{
    /**
    * @Route("/find/{firstLetters}", name="city_find", methods={"GET"})
    */
    public function cities(Request $request, CityRepository $cityRepository)
    {
        // Create an API-like method which send cities from DB begining with
        // some letters. This is has been created for the autocompletion feature
        $firstLetters = $request->get("firstLetters");
        $cities = $cityRepository->getCitiesStartingWith($firstLetters);
        $jsonCities = $this->encodeCitiesToJson($cities);
        return new Response($jsonCities, 200, ['Content-Type' => 'application/json']);
    }


    private function encodeCitiesToJson(array $cities){
        //Encode list cities to json
        $jsonEncoder = new JsonEncoder();
        $jsonCities = array();
        foreach($cities as $city){
            $cityData = array(
                'id' => $city->getId(),
                'name' => $city->getName(),
            );
            \array_push($jsonCities, $cityData);
        }
        return $jsonEncoder->encode(['cities' => $jsonCities], $format = 'json');
    }
}
