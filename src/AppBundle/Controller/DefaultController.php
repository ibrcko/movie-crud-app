<?php
namespace AppBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    // gathers page parameter from request defaulted to 1
    // selects all movies from db
    // creates pagerfanta object with gathered movies
    // sets pagerfanta current page to gathered page parameter
    // renders index view
    public function indexAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $entityManager = $this->getDoctrine()->getManager();
        $queryBuilder = $entityManager->createQueryBuilder();
            $queryBuilder->select('m')
            ->from('AppBundle\Entity\Movie' , 'm');

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setCurrentPage($page);

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'my_pager' => $pagerfanta,
        ]);
    }
}
