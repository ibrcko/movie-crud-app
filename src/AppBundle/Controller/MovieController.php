<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Movie;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class MovieController extends Controller
{
    /**
     * @Route("/form/movie", name="form_movie")
     */
    // method that renders movie form for creation
    public function formMovie(Request $request)
    {
        return $this->render('forms/movie-form.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => $request->query->get('error'),
            ]);
    }

    /**
     * @Route("/create/movie", name="create")
     */
    // method that gathers parameters from request
    // checks that request movie name and year are unique
    // creates new movie with gathered parameters
    // redirects to homepage
    public function createMovie(Request $request)
    {
        $name = $request->request->get('name');
        $year = $request->request->get('year');
        $description = $request->request->get('description');

        $entityManager = $this->getDoctrine()->getManager();
        $movies = $this->getDoctrine()
            ->getRepository(Movie::class)
            ->findAll();

        foreach ($movies as $movie)
        {
            if ($name == $movie->getName() && $year == $movie->getYear())
                return $this->redirectToRoute('form_movie', ['error' => 1]);
        }

        $movie = new Movie();
        $movie->setName($name);
        $movie->setYear($year);
        $movie->setDescription($description);

        $entityManager->persist($movie);

        $entityManager->flush();

        return $this->redirectToRoute('homepage');

    }

    /**
     * @Route("/read/movie", name="read_movie")
     */
    // fetches movie from the db by movieId parameter
    // fetches crew members related to the movie
    // renders movie view
    public function readMovie(Request $request)
    {
        $movieId = $request->query->get('movieId');
        $movie = $this->getDoctrine()
            ->getRepository(Movie::class)
            ->find($movieId);

        if (!$movie) {
            throw $this->createNotFoundException(
                'No movie found for id: ' . $movieId
            );
        }

        $crewMembers = $movie->getCrewMembers();

        return $this->render('read/movie.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'movie' => $movie,
            'crewMembers' => $crewMembers,
            'error' => $request->query->get('error'),
        ]);
    }

    /**
     * @Route("/update/movie", name="update_movie")
     */
    // gathers request parameters
    // fetches movie by movieId from the db
    // fetches all movies from the db
    // checks that request movie name and year are unique
    // updates movie
    // redirects to read_movie
    public function updateMovie(Request $request)
    {
        $movieId = $request->request->get('movieId');

        $name = $request->request->get('name');
        $year = $request->request->get('year');
        $description = $request->request->get('description');

        $entityManager = $this->getDoctrine()->getManager();
        $movie = $entityManager->getRepository(Movie::class)->find($movieId);

        $movies = $this->getDoctrine()
            ->getRepository(Movie::class)
            ->findAll();

        foreach ($movies as $movie)
        {
            if ($movieId != $movie->getId() && $name == $movie->getName() && $year == $movie->getYear())
                return $this->redirectToRoute('read_movie', ['movieId' => $movieId, 'error' => 1]);
        }

        $movie->setName($name);
        $movie->setYear($year);
        $movie->setDescription($description);
        $entityManager->flush();

        return $this->redirectToRoute('read_movie', ['movieId' => $movie->getId()]);

    }

    /**
     * @Route("/delete/movie", name="delete")
     */
    // gathers request parameters
    // fetches movie by movieId from the db
    // fetches crew members related to the movie
    // deletes crew members
    // deletes movie
    // redirects to homepage
    public function deleteMovie(Request $request)
    {
        $movieId = $request->query->get('movieId');

        $entityManager = $this->getDoctrine()->getManager();
        $movie = $entityManager->getRepository(Movie::class)->find($movieId);

        $crewMembers = $movie->getCrewMembers();

        $this->deleteCrewMembers($crewMembers);

        if (!$movie) {
            throw $this->createNotFoundException(
                'No movie found for id: ' . $movieId
            );
        }

        $entityManager->remove($movie);
        $entityManager->flush();

        return $this->redirectToRoute('homepage');

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