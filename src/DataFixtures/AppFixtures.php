<?php

namespace App\DataFixtures;


use App\Entity\City;
use App\Entity\User;
use App\Entity\Trip;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    private $encoder;
    private $users;
    private $RENNES_CITY_REFERENCE;
    private $NANTES_CITY_REFERENCE;
    private $REZE_CITY_REFERENCE;

    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
        $this->users = [];
    }

    public function load(ObjectManager $manager){
        $cities = $this->loadCities($manager);
        $this->loadUsers($manager, $cities);
    }

    //City list have been generated from the get_cities script
    public function loadUsers(ObjectManager $manager, array $cities)
    {
        $may = strtotime("2020-05-01 00:00:00");
        $july = strtotime("2020-07-01 00:00:00");
        foreach ($this->getUserData() as [$login, $mail, $password, $surname, $role]) {
            $user = new User();
            $user->setLogin($login);
            $user->setMail($mail);
            $user->setSurname($surname);
            $user->setPassword($this->encoder->encodePassword($user, $password));
            $user->setRoles($role);
            $manager->persist($user);
            \array_push($this->users, $user);

            foreach (range(1, 30) as $i) {
                $trip = new Trip();
                $trip->setDriver($user);
                #Make sure trip is from two different cities
                $fromCity = $cities[rand(0, 274)];
                do{
                    $toCity =  $cities[rand(0, 274)];
                }while($toCity->getId() == $fromCity->getid());
                $trip->setFromC($fromCity);
                $trip->setToC($toCity);
                $trip->setMaxSeats(rand(1, 4));
                $trip->setSeatPrice(rand(10, 40));
                $start = new \DateTime(date("Y-m-d H:i:s", rand($may, $july)));
                $trip->setStart($start);
                $end = clone $start;
                $end = $end->add(new \DateInterval("PT".rand(1, 7)."H".rand(1, 59)."S"));
                $trip->setEnd($end);
                $now = new \DateTime();
                $trip->setCreated( $now->add(new \DateInterval("PT".rand(1, 59)."S")));
                $manager->persist($trip);
            }
        }

        # Hard coded trip from Nantes to Rennes and Rezé to nantes for démo purpose
        $tripNtoR = new Trip();
        $tripNtoR->setDriver($this->users[0]);
        $tripNtoR->setFromC($this->NANTES_CITY_REFERENCE);
        $tripNtoR->setToC($this->RENNES_CITY_REFERENCE);
        $tripNtoR->setMaxSeats(2);
        $tripNtoR->setSeatPrice(10);
        $start = new \DateTime(date("Y-m-d H:i:s", strtotime("2020-06-12 14:30:00")));
        $tripNtoR->setStart($start);
        $end = new \DateTime(date("Y-m-d H:i:s", strtotime("2020-06-12 15:50:00")));
        $tripNtoR->setEnd($end);
        $tripNtoR->setCreated(new \DateTime());
        $manager->persist($tripNtoR);


        $tripRtoR = new Trip();
        $tripRtoR->setDriver($this->users[1]);
        $tripRtoR->setFromC($this->REZE_CITY_REFERENCE);
        $tripRtoR->setToC($this->RENNES_CITY_REFERENCE);
        $tripRtoR->setMaxSeats(3);
        $tripRtoR->setSeatPrice(11);
        $start = new \DateTime(date("Y-m-d H:i:s", strtotime("2020-06-12 14:35:00")));
        $tripRtoR->setStart($start);
        $end = new \DateTime(date("Y-m-d H:i:s", strtotime("2020-06-12 15:55:00")));
        $tripRtoR->setEnd($end);
        $tripRtoR->setCreated(new \DateTime());
        $manager->persist($tripRtoR);


        $manager->flush();
    }

    //City list have been generated from the get_cities script
    public function loadCities(ObjectManager $manager)
    {
        $tab_cities = [];
        foreach ($this->getCityData() as [$name, $longitude, $latitude]) {
            $city = new City();
            $city->setname($name);
            $city->setLongitude($longitude);
            $city->setLatitude($latitude);
            $manager->persist($city);
            if ($name == "Nantes"){
                $this->NANTES_CITY_REFERENCE = $city;
            }elseif($name == "Rennes"){
                $this->RENNES_CITY_REFERENCE = $city;
            }elseif($name == "Rezé"){
                $this->REZE_CITY_REFERENCE = $city;
            }
            \array_push($tab_cities, $city);
        }
        $manager->flush();
        return $tab_cities;
    }
    public function getUserData(){
        return [
            ['Hylectrif', 'hylectrif@gmail.com', 'Hylectrif', 'Samuel', ['ROLE_ADMIN']],
            ['SuperSim', 'supersim@gmail.com', 'SuperSim', 'Simon', []],
            ['CaptainSoap', 'captainsoap@gmail.com', 'CaptainSoap', 'Gaëtan', ['ROLE_ADMIN']],
            ['lastar', 'lastar@gmail.com', 'lastar', 'Elyne', ['ROLE_ADMIN']],
        ];
    }
    public function getCityData(){
        return [
            ["Paris", 2.3514616, 48.8566969],
            ["Marseille", 5.3699525, 43.2961743],
            ["Lyon", 4.8320114, 45.7578137],
            ["Toulouse", 1.4442469, 43.6044622],
            ["Nice", 7.2683912, 43.7009358],
            ["Nantes", -1.5541362, 47.2186371],
            ["Montpellier", 3.8767337, 43.6112422],
            ["Strasbourg", 7.7507127, 48.584614],
            ["Bordeaux", -0.5800364, 44.841225],
            ["Lille", 3.0635282, 50.6365654],
            ["Rennes", -1.6800198, 48.1113387],
            ["Reims", 4.031926, 49.2577886],
            ["Saint-étienne", 4.3873058, 45.4401467],
            ["Toulon", 5.9304919, 43.1257311],
            ["Le havre", 0.1079732, 49.4938975],
            ["Grenoble", 5.7357819, 45.1875602],
            ["Dijon", 5.0414701, 47.3215806],
            ["Angers", -0.5515588, 47.4739884],
            ["Nîmes", 4.3600687, 43.8374249],
            ["Saint-denis", 2.3580232, 48.935773],
            ["Villeurbanne", 4.8869339, 45.7733105],
            ["Clermont-ferrand", 3.0819427, 45.7774551],
            ["Le mans", 0.1967379, 48.0073498],
            ["Aix-en-provence", 5.4474738, 43.5298424],
            ["Brest", -4.4860088, 48.3905283],
            ["Tours", 0.6889268, 47.3900474],
            ["Amiens", 2.2956951, 49.8941708],
            ["Limoges", 1.2644847, 45.8354243],
            ["Annecy", 6.1288847, 45.8992348],
            ["Perpignan", 2.8953121, 42.6985304],
            ["Boulogne-billancourt", 2.240206, 48.8356649],
            ["Orléans", -75.5100002, 45.4810323],
            ["Metz", 6.1763552, 49.1196964],
            ["Besançon", 6.0243622, 47.2380222],
            ["Saint-denis", 2.3580232, 48.935773],
            ["Argenteuil", 2.2481797, 48.9479069],
            ["Rouen", 1.0939658, 49.4404591],
            ["Montreuil", 1.7631125, 50.4638918],
            ["Mulhouse", 7.3389275, 47.7467],
            ["Caen", -0.3690815, 49.1828008],
            ["Saint-paul", 2.0100016, 49.4318046],
            ["Nancy", 6.1834097, 48.6937223],
            ["Nouméa", 166.442419, -22.2745264],
            ["Tourcoing", 3.1605714, 50.7235038],
            ["Roubaix", 3.1741734, 50.6915893],
            ["Nanterre", 2.2071267, 48.8924273],
            ["Vitry-sur-seine", 2.39164, 48.7876],
            ["Avignon", 4.8059012, 43.9492493],
            ["Créteil", 2.4530731, 48.7771486],
            ["Poitiers", 0.340196, 46.5802596],
            ["Dunkerque", 2.3772525, 51.0347708],
            ["Aubervilliers", 2.3821895, 48.9146078],
            ["Versailles", 2.1266886, 48.8035403],
            ["Aulnay-sous-bois", 2.499789, 48.934231],
            ["Asnières-sur-seine", 2.2890454, 48.9105948],
            ["Colombes", 2.2543577, 48.922788],
            ["Saint-pierre", 7.4718731, 48.3832725],
            ["Courbevoie", 2.2561602, 48.8953328],
            ["Fort-de-france", -61.0676724, 14.6027962],
            ["Cherbourg-en-cotentin", -1.60901502653337, 49.62727515],
            ["Le tampon", 55.5164178, -21.2774285],
            ["Rueil-malmaison", 2.1802832, 48.87778],
            ["Champigny-sur-marne", 2.5107384, 48.8137759],
            ["Béziers", 3.2131307, 43.3426562],
            ["Pau", -0.3685668, 43.2957547],
            ["La rochelle", -1.1520434, 46.1591126],
            ["Saint-maur-des-fossés", 2.4853015, 48.8033057],
            ["Calais", 1.87468, 50.9488],
            ["Cannes", 7.0134418, 43.5515198],
            ["Antibes", 7.10905, 43.5836],
            ["Mamoudzou", 45.2279908, -12.7805856],
            ["Drancy", 2.4455201, 48.9229803],
            ["Ajaccio", 8.7376029, 41.9263991],
            ["Mérignac", -0.6469599, 44.8422361],
            ["Saint-nazaire", -2.2138905, 47.2733517],
            ["Colmar", 7.3579641, 48.0777517],
            ["Issy-les-moulineaux", 2.273457, 48.8250508],
            ["Noisy-le-grand", 2.5519571, 48.8493972],
            ["Évry-courcouronnes", 2.4345980281360133, 48.6284131],
            ["Vénissieux", 4.8855966, 45.6977109],
            ["Cergy", 2.0388736, 49.0527528],
            ["Bourges", 2.398932, 47.0805693],
            ["Levallois-perret", 2.2881683, 48.892956],
            ["La seyne-sur-mer", 5.8788948, 43.1007714],
            ["Pessac", -0.6308396, 44.805615],
            ["Valence", -0.3759513, 39.4699014],
            ["Villeneuve-d'ascq", 3.1314002, 50.6193174],
            ["Quimper", -4.1024782, 47.9960325],
            ["Antony", 2.2959423, 48.753554],
            ["Ivry-sur-seine", 2.3872525, 48.8122302],
            ["Troyes", 4.0746257, 48.2971626],
            ["Cayenne", -52.3258307, 4.9371143],
            ["Clichy", 2.30551, 48.9026],
            ["Montauban", 1.3549991, 44.0175835],
            ["Neuilly-sur-seine", 2.2695658, 48.884683],
            ["Chambéry", 5.9203636, 45.5662672],
            ["Niort", -0.4645212, 46.3239455],
            ["Sarcelles", 2.3796245, 48.9960813],
            ["Pantin", 2.4019804, 48.8965023],
            ["Lorient", -3.3660907, 47.7477336],
            ["Le blanc-mesnil", 2.4631476, 48.9385489],
            ["Saint-andré", 0.8514079, 43.273316],
            ["Beauvais", 2.0823355, 49.4300997],
            ["Maisons-alfort", 2.4309703, 48.8012045],
            ["Hyères", 6.1301614, 43.1202573],
            ["Épinay-sur-seine", 2.3145039, 48.9525181],
            ["Meaux", 2.8773541, 48.9582708],
            ["Chelles", 3.0344372, 49.3549458],
            ["Villejuif", 2.3633048, 48.7921098],
            ["Narbonne", 3.0042121, 43.1837661],
            ["La roche-sur-yon", -1.4269698, 46.6705431],
            ["Cholet", -0.8801349, 47.0617293],
            ["Saint-quentin", 3.2876843, 49.8465253],
            ["Bobigny", 2.4452231, 48.906387],
            ["Les abymes", -61.5057749, 16.2706436],
            ["Saint-louis", -90.1994097, 38.6268039],
            ["Bondy", 2.48291, 48.9031],
            ["Vannes", -2.7599079, 47.6586772],
            ["Clamart", 2.2630292, 48.800368],
            ["Fontenay-sous-bois", 2.4749347, 48.8490721],
            ["Fréjus", 6.7360182, 43.4330308],
            ["Arles", 4.6309653, 43.6776223],
            ["Sartrouville", 2.1585037, 48.9409016],
            ["Corbeil-essonnes", 2.4818087, 48.6137734],
            ["Bayonne", -1.475099, 43.4933379],
            ["Saint-ouen-sur-seine", 2.334267, 48.911729],
            ["Sevran", 2.5257254, 48.9392142],
            ["Cagnes-sur-mer", 7.1513834, 43.6612012],
            ["Massy", 1.399492, 49.6902106],
            ["Grasse", 6.9239103, 43.6589011],
            ["Montrouge", 2.3194375, 48.8188544],
            ["Vincennes", 2.4396714, 48.8474508],
            ["Laval", -0.7723499, 48.0710377],
            ["Vaulx-en-velin", 4.9206153, 45.7784255],
            ["Albi", 2.147899, 43.9277552],
            ["Suresnes", 2.2283997, 48.8710994],
            ["Martigues", 5.0548176, 43.4057279],
            ["Évreux", 1.1510164, 49.0268903],
            ["Belfort", 6.8628942, 47.6379599],
            ["Brive-la-gaillarde", 1.5332389, 45.1584982],
            ["Gennevilliers", 2.2940122, 48.9254221],
            ["Charleville-mézières", 4.7206939, 49.7735712],
            ["Saint-herblain", -1.6346964, 47.2233007],
            ["Aubagne", 5.5703031, 43.2924385],
            ["Saint-priest", 4.54685, 44.7177],
            ["Rosny-sous-bois", 2.4875193, 48.8716626],
            ["Saint-malo", -2.015418, 48.6454528],
            ["Blois", 1.3337639, 47.5876861],
            ["Carcassonne", 2.3491069, 43.2130358],
            ["Bastia", 9.452542, 42.7065505],
            ["Salon-de-provence", 5.0980225, 43.6405237],
            ["Meudon", 2.2385432, 48.8126688],
            ["Choisy-le-roi", 2.4093664, 48.7630238],
            ["Chalon-sur-saône", 4.8529605, 46.7888997],
            ["Châlons-en-champagne", 4.3628851, 48.9566218],
            ["Saint-germain-en-laye", 2.0917721820108612, 48.935606899999996],
            ["Puteaux", 2.2368863, 48.8841522],
            ["Livry-gargan", 2.5298854, 48.917335],
            ["Saint-brieuc", -2.7602707, 48.5141594],
            ["Mantes-la-jolie", 1.7140683, 48.9891971],
            ["Noisy-le-sec", 2.4516055, 48.8897751],
            ["Les sables-d'olonne", -1.7924568493808075, 46.5002031],
            ["Alfortville", 2.4197113, 48.8051624],
            ["Châteauroux", 1.6957099, 46.8047],
            ["Valenciennes", 3.5234846, 50.3579317],
            ["Sète", 3.6959771, 43.4014434],
            ["Caluire-et-cuire", 4.8423304, 45.7969952],
            ["Istres", 4.9884323, 43.5139051],
            ["La courneuve", 2.3896123, 48.9267236],
            ["Garges-lès-gonesse", 2.399024, 48.9703841],
            ["Saint-laurent-du-maroni", -54.0306793, 5.4989648],
            ["Talence", -0.5879629, 44.8088438],
            ["Angoulême", 0.1576745, 45.6512748],
            ["Castres", 3.2383333, 49.8044444],
            ["Bron", 4.9092352, 45.7337532],
            ["Bourg-en-bresse", 5.2250324, 46.2051192],
            ["Tarbes", 0.0781021, 43.232858],
            ["Le cannet", -0.05124516516193034, 43.61666715],
            ["Rezé", -1.5695287, 47.1905456],
            ["Arras", 2.7772211, 50.291048],
            ["Wattrelos", 3.2153907, 50.7016015],
            ["Bagneux", 3.279444, 49.458056],
            ["Gap", 6.0820018, 44.5611978],
            ["Boulogne-sur-mer", 1.6118771, 50.7259985],
            ["Thionville", 6.1675872, 49.3579272],
            ["Alès", 4.0852818, 44.1253665],
            ["Compiègne", 2.8263171, 49.4179497],
            ["Melun", 2.6608169, 48.539927],
            ["Le lamentin", -61.0018145, 14.614557],
            ["Douai", 3.0804641, 50.3675677],
            ["Gagny", 2.5361783, 48.8853708],
            ["Draguignan", 6.4627333, 43.5374662],
            ["Montélimar", 4.750318, 44.5579391],
            ["Colomiers", 1.3282149, 43.6121001],
            ["Anglet", -1.5149935, 43.4813927],
            ["Stains", 2.3825154, 48.9565669],
            ["Marcq-en-barœul", 3.1043032, 50.6767018],
            ["Chartres", 1.4881434, 48.4438601],
            ["Saint-martin-d'hères", 5.75448, 45.1836683],
            ["Joué-lès-tours", 0.6622524, 47.3510905],
            ["Saint-benoît", 2.0639828, 43.0169012],
            ["Pontault-combault", 2.6068064, 48.8009132],
            ["Saint-joseph", -1.529802, 49.5344534],
            ["Poissy", 2.0428293, 48.927439],
            ["Châtillon", 5.7302769, 46.6591772],
            ["Villefranche-sur-saône", 4.726611, 45.9864749],
            ["Échirolles", 5.71927, 45.1437],
            ["Villepinte", 2.0855714, 43.2814917],
            ["Franconville", 6.4527267, 48.5000906],
            ["Savigny-sur-orge", 2.3458417, 48.6794572],
            ["Sainte-geneviève-des-bois", 2.3259129, 48.6407872],
            ["Tremblay-en-france", 2.5589558, 48.9802035],
            ["Conflans-sainte-honorine", 2.0949768, 48.9938791],
            ["Annemasse", 6.2341093, 46.1934005],
            ["Bagnolet", 2.4173658, 48.8688199],
            ["Creil", 2.4733771, 49.2607914],
            ["Montluçon", 2.6067229, 46.3399276],
            ["Palaiseau", 2.2453842, 48.7144587],
            ["Saint-martin", 6.7539988, 48.5683066],
            ["La ciotat", 5.6062495, 43.1758591],
            ["Saint-raphaël", 1.0747165, 45.3043079],
            ["Neuilly-sur-marne", 2.5290045, 48.8565557],
            ["Saint-chamond", 4.5098046, 45.4748298],
            ["Thonon-les-bains", 6.4779448, 46.3731303],
            ["Auxerre", 3.570579, 47.7961287],
            ["Haguenau", 7.7885978, 48.8172236],
            ["Roanne", 4.0729178, 46.0345572],
            ["Athis-mons", 2.3890941, 48.7079028],
            ["Le port", 1.3740316, 42.8662832],
            ["Villenave-d'ornon", -0.5595029, 44.7737872],
            ["Le perreux-sur-marne", 2.5077111, 48.840627],
            ["Sainte-marie", 6.6967773, 47.5091856],
            ["Mâcon", 4.8322266, 46.3036683],
            ["Agen", 0.6176112, 44.2015827],
            ["Saint-leu", 55.2881932, -21.1677694],
            ["Villeneuve-saint-georges", 2.44782, 48.7305272],
            ["Meyzieu", 5.0044767, 45.7764637],
            ["Vitrolles", 5.9496043, 44.4347105],
            ["Châtenay-malabry", 2.2775316, 48.7670404],
            ["Romans-sur-isère", 5.0528681, 45.0458886],
            ["La possession", 55.3361334, -20.9269658],
            ["Nevers", 3.1577203, 46.9876601],
            ["Montigny-le-bretonneux", 2.0381228, 48.7698937],
            ["Marignane", 5.2146275, 43.4162729],
            ["Nogent-sur-marne", 2.4917292, 48.8388009],
            ["Six-fours-les-plages", 5.8393953, 43.0935105],
            ["Les mureaux", 1.909928, 48.9931578],
            ["Trappes", 1.9988356, 48.7760957],
            ["Cambrai", 3.2356613, 50.1759602],
            ["Koungou", 45.2067459, -12.735753],
            ["Houilles", 2.1868998, 48.9229416],
            ["Matoury", -52.3268695, 4.8496712],
            ["Schiltigheim", 7.7484488, 48.6047148],
            ["Châtellerault", 0.545812, 46.8180047],
            ["Dumbéa", 166.4580117, -22.1676665],
            ["Épinal", 6.4503643, 48.1747684],
            ["Vigneux-sur-seine", 2.4172117, 48.6998552],
            ["Plaisir", 1.9476363, 48.817399],
            ["Lens", 2.8319805, 50.4291723],
            ["L'haÿ-les-roses", 2.3373021, 48.7790514],
            ["Le chesnay-rocquencourt", 2.130235886256541, 48.82841365],
            ["Saint-médard-en-jalles", -0.7170764, 44.8958887],
            ["Viry-châtillon", 2.3750351, 48.6708729],
            ["Cachan", 2.3340758, 48.7945413],
            ["Dreux", 1.3684254, 48.7358807],
            ["Baie-mahault", -61.5870766, 16.2679458],
            ["Liévin", 2.7738, 50.4245],
            ["Pontoise", 2.1008067, 49.0508845],
            ["Malakoff", 2.3019814, 48.8211559],
            ["Goussainville", 2.4733628, 49.0323168],
            ["Charenton-le-pont", 2.4159508, 48.8198479],
            ["Pierrefitte-sur-seine", 2.3601022, 48.9637589],
            ["Chatou", 2.1573695, 48.8897044],
            ["Rillieux-la-pape", 4.8994366, 45.823514],
            ["Vandœuvre-lès-nancy", 6.1724618, 48.6599676],
        ];
    }
}