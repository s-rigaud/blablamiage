<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\TripRepository;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ObjectManager;


/**
 * @Route("/comment")
*/
class CommentController extends AbstractController
{

    private $om;
    private $security;

    public function __construct(Security $security, ObjectManager $om){
        $this->security = $security;
        $this->om = $om;
    }

    /**
     * Create a new comment
     * @Route("/create", name="comment_create", methods={"GET|POST"})
     */
    public function create(Request $request, TripRepository $tripRepository): Response
    {
        $tripId = $request->get('trip');
        $trip = $tripRepository->find($tripId);
        $user = $this->getUser();
        $comment = new Comment();
        $comment->setTrip($trip);
        $comment->setUser($user);
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        //Ensure that that trip and user exist
        if ($form->isSubmitted() && $comment->getText()){
            $this->om->persist($comment);
            $this->om->flush();
            $this->addFlash('success', 'The comment has been created');
        }else{
            $this->addFlash('warning', 'Something went wrong when creating your comment');
        }
        return $this->redirectToRoute('trip_view', ['id' => $trip->getId()]);
    }

    /**
     * Method in order to edit comment
     * @Route("/{id}", name="comment_edit", methods={"POST"})
     */
    public function edit(Comment $comment, Request $request, CommentRepository $commentRepository, ObjectManager $objectManager): Response
    {
        //checks if the driver's trip is the logged user
        if($comment->getUser() == $this->getUser()){
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);
            if ($form->isSubmitted()){
                //Send the values
                $this->om->flush();
            }
        }else{
            $this->addFlash('warning', "You can not edit someone else comment");
        }
        return $this->redirectToRoute('trip_view', ['id' => $comment->getTrip()->getId()]);
    }


     /**
     * Allows a logged user to delete one of his comment
     * @Route("/{id}", name="comment_delete", methods={"DELETE"})
     */
    public function delete(Comment $comment, Request $request): Response
    {
        if($comment->getUser() == $this->getUser() && $this->isCsrfTokenValid('delete'.$comment->getId(), $request->get('_token'))){
                $this->om->remove($comment);
                $this->om->flush();
                $this->addFlash('success', "The comment has been deleted");
        }else{
            $this->addFlash('warning', "You can not delete someone else trip");
        }
        return $this->redirectToRoute('trip_view', ['id' => $comment->getTrip()->getId()]);
    }
}
