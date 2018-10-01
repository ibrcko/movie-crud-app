<?php
namespace AppBundle\Controller;


use AppBundle\Entity\CrewMember;
use AppBundle\Entity\Movie;
use AppBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CrewMemberController extends Controller
{
    /**
     * @Route("/form/crew-member", name="form_crew_member")
     */
    public function formCrewMember(Request $request)
    {
        $roles = $this->getDoctrine()
            ->getRepository(Role::class)
            ->findAll();

        return $this->render('forms/crew-form.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'roles' => $roles,
            'movieId' => $request->query->get('movieId')
        ]);
    }

    /**
     * @Route("/create/crew-member", name="create_crew_member")
     */
    public function createCrewMember(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $crewMember = new CrewMember();
        $crewMember->setFirstName($request->request->get('first-name'));
        $crewMember->setLastName($request->request->get('last-name'));
        $crewMember->setBirthDate($request->request->get('birth-date'));

        $role = $this->getDoctrine()
            ->getRepository(Role::class)
            ->find($request->request->get('role'));
        $movie = $this->getDoctrine()
            ->getRepository(Movie::class)
            ->find($request->request->get('movie-id'));
        $crewMember->setRole($role);
        $crewMember->setMovie($movie);

        $entityManager->persist($crewMember);

        $entityManager->flush();

        return $this->redirectToRoute('read_movie', ['movieId' => $request->request->get('movie-id')]);


    }

    /**
     * @Route("read/crew-members", name="read_crew_members")
     */
    public function readCrewMembers()
    {
        $crewMembers = $this->getDoctrine()
            ->getRepository(CrewMember::class)
            ->findAll();

        return $this->render('read/crew.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'crewMembers' => $crewMembers,
        ]);
    }

    /**
     * @Route("/delete/crew-member", name="delete_crew_member")
     */
    public function deleteCrewMember(Request $request)
    {
        $crewMemberId = $request->query->get('memberId');
        $movieId = $request->query->get('movieId');

        $entityManager = $this->getDoctrine()->getManager();
        $crewMember = $entityManager->getRepository(CrewMember::class)->find($crewMemberId);

        if (!$crewMember) {
            throw $this->createNotFoundException(
                'No crew member found for id: ' . $crewMemberId
            );
        }

        $entityManager->remove($crewMember);
        $entityManager->flush();

        return $this->redirectToRoute('read_movie', ['movieId' => $movieId]);

    }

}