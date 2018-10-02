<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    /**
     * @Route("/form/role", name="form_role")
     */
    // method that renders role form for creation
    public function formRole(Request $request)
    {
        return $this->render('forms/role-form.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


    /**
     * @Route("/create/role", name="create_role")
     */
    // method that gathers parameters from request
    // creates new role with gathered parameters
    // redirects to read_roles
    public function createRole(Request $request)
    {
        $roleParam = $request->request->get('role');
        $entityManager = $this->getDoctrine()->getManager();

        $role = new Role();
        $role->setRole($roleParam);

        $entityManager->persist($role);

        $entityManager->flush();

        return $this->redirectToRoute('read_roles');
    }

    /**
     * @Route("read/roles", name="read_roles")
     */
    // fetches all roles from the db
    // renders role view
    public function readRoles(Request $request)
    {
        $roles = $this->getDoctrine()
            ->getRepository(Role::class)
            ->findAll();

        return $this->render('read/role.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'roles' => $roles,
        ]);
    }

    /**
     * @Route("/delete/role", name="delete_role")
     */
    // gathers request parameters
    // fetches role by roleId from the db
    // fetches crew members related to the role
    // deletes crew members
    // deletes role
    // redirects to read_roles
    public function deleteRole(Request $request)
    {
        $roleId = $request->query->get('roleId');

        $entityManager = $this->getDoctrine()->getManager();
        $role = $entityManager->getRepository(Role::class)->find($roleId);

        if (!$role) {
            throw $this->createNotFoundException(
                'No role found for id: ' . $roleId
            );
        }

        $crewMembers = $role->getCrewMembers();
        $this->deleteCrewMembers($crewMembers);

        $entityManager->remove($role);
        $entityManager->flush();

        return $this->redirectToRoute('read_roles');

    }

    // deletes every crew member that has been provided through argument
    private function deleteCrewMembers($crewMembers)
    {
        $entityManager = $this->getDoctrine()->getManager();

        foreach ($crewMembers as $member) {
            $entityManager->remove($member);
            $entityManager->flush();
        }
    }
}