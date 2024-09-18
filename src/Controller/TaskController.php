<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $tasks = $em->getRepository(Task::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('task/index.html.twig', ['tasks' => $tasks]);
    }
    #[Route('/tasks/new', name: 'task_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $task = new Task();

        // Définir la date de création à la date actuelle
        $task->setCreatedAt(new \DateTime());

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($task);
            $em->flush();
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function edit(Request $request, EntityManagerInterface $em, Task $task): Response
    {
        // Crée le formulaire sans modifier 'createdAt'
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Met à jour la date de création avec la date actuelle
            $task->setCreatedAt(new \DateTime());

            $em->flush();  // L'entité est déjà gérée, donc pas besoin de `persist`
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em, Task $task): Response
    {
        // Vérification du token CSRF pour éviter les suppressions non sécurisées
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $em->remove($task);
            $em->flush();
        }

        return $this->redirectToRoute('task_list');
    }


}
