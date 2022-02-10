<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use cebe\markdown\Markdown;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    private $taskes;

    public function __construct(TaskRepository $taskes)
    {
        $this->taskes = $taskes;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(Markdown $parser): Response
    {
        $taskes = $this->taskes->findAll();

        $parsedTask = [];
        foreach ($taskes as $task) {
           $parsedTask[] = [
              'id' => $task->getId(),
              'status' => $task->getStatus(),
              'name' => $task->getName(),
              'description' => $parser->parse($task->getDescription())
           ];
        }

        return $this->render('homepage.html.twig', [
           'taskes' => $parsedTask
        ]);
    }

    /**
     * @Route("/create-task", name="create_task", methods={"POST"})
     */
    public function createTask(Request $request, ManagerRegistry $doctrine): Response
    {
        $task = new Task();
        $task->setName($request->request->get('name'));
        $task->setDescription($request->request->get('description'));

        $em = $doctrine->getManager();
        $em->persist($task);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/switch-task/{id}", name="switch_task")
     */
    public function switchTask(ManagerRegistry $doctrine, $id): Response
    {
        $em = $doctrine->getManager();
        $task = $em->getRepository(Task::class)->find($id);
        $task->setStatus( ! $task->getStatus() );

        $em->flush();

        return $this->redirectToRoute('homepage');
    }

   /**
    * @Route("/edit-task/{id}", name="edit_task", methods={"GET", "POST"})
    */
    public function editTask(ManagerRegistry $doctrine, $id): Response
    {
       $em = $doctrine->getManager();
       $task = $em->getRepository(Task::class)->find($id);

       $task->setName( $task->getName() );
       $task->setDescription( $task->getDescription() );

       return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/delete-task/{id}", name="delete_task")
     */
    public function deleteTask(ManagerRegistry $doctrine, $id): Response
    {
        $em = $doctrine->getManager();
        $task = $em->getRepository(Task::class)->find($id);

        $em->remove($task);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
}