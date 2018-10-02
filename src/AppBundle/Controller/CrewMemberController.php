<?php
namespace AppBundle\Controller;


use AppBundle\Entity\CrewMember;
use AppBundle\Entity\Movie;
use AppBundle\Entity\Role;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
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
    // method that renders crew members form for creation
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
    // method that gathers parameters from request
    // creates new crew member with gathered parameters
    // fetches role from db by role parameter
    // fetches movie from db by movie-id parameter
    // sets crew members role and movie
    // redirects to read_movie
    public function createCrewMember(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $firstName = $request->request->get('first-name');
        $lastName = $request->request->get('last-name');
        $birthDate = $request->request->get('birth-date');

        $crewMember = new CrewMember();
        $crewMember->setFirstName($firstName);
        $crewMember->setLastName($lastName);
        $crewMember->setBirthDate($birthDate);

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
    // fetches all crew members from the db
    // creates pagerfanta object with gathered crew members
    // renders crew view
    public function readCrewMembers(Request $request)
    {
        $page = $request->query->get('page', 1);
        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('cm')
            ->from('AppBundle\Entity\CrewMember' , 'cm')
            ->orderBy('cm.id');

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setCurrentPage($page);

        return $this->render('read/crew.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'my_pager' => $pagerfanta,
        ]);
    }

    /**
     * @Route("/delete/crew-member", name="delete_crew_member")
     */
    // gathers request parameters
    // fetches crew member by memberId from the db
    // deletes crew member
    // redirects to read_movie
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