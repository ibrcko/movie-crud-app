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
    public function formRole(Request $request)
    {
        return $this->render('forms/role-form.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


    /**
     * @Route("/create/role", name="create_role")
     */
    public function createRole(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $role = new Role();
        $role->setRole($request->request->get('role'));

        $entityManager->persist($role);

        $entityManager->flush();

        return $this->redirectToRoute('read_roles');
    }

    /**
     * @Route("read/roles", name="read_roles")
     */
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

    private function deleteCrewMembers($crewMembers)

    {
        $entityManager = $this->getDoctrine()->getManager();

        foreach ($crewMembers as $member) {
            $entityManager->remove($member);
            $entityManager->flush();
        }
    }
}