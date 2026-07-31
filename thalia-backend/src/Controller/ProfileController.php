<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ProfileFormType;
use App\Service\CrudManagerService;
use App\Service\FileUpLoader;
use App\Service\PasswordManagerService;
use App\Service\RoleGetterService;
use App\Service\UserContextService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/profile')]
#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
     public function __construct(
        private CrudManagerService $crudManager,
        private FileUpLoader $fileUpLoader,
        private UserContextService $userContext,
        private RoleGetterService $roleGetter,
        private ParameterBagInterface $params )
    {}
    #[Route('', name: 'profile_index', methods: ['GET'])]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'userRoles' => $this->roleGetter->formatUserRoles($user),
        ]);
    }

    #[ Route('/edit', name:'profile_edit', methods:['GET', 'POST'])]
    public function edit(Request $request): Response
    {   
        /** @var User $user */
        $user = $this->getUser();

        $oldAvatar = $user->getAvatarFilename();
        $formProfile = $this->createForm(ProfileFormType::class, $user, [
            'user_organization' => $this->userContext->getOrganization(),
        ]);

        $formProfile->handleRequest($request);

        if($formProfile->isSubmitted() && $formProfile->isValid()){
            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $formProfile->get('avatar')->getData();
            if ($avatarFile) {
                $newFilename = $this->fileUpLoader->upload($avatarFile, $this->params->get('profiles_directory'));
                
                if ($newFilename) {
                    //  Suppression de l'ancienne photo de profil
                    if ($oldAvatar) {
                        $oldFilePath = $this->params->get('profiles_directory') . '/' . $oldAvatar;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    $user->setAvatarFilename($newFilename);
                }
            } else {
                // Si aucune image n'est soumise, on réinjecte l'ancienne 
                $user->setAvatarFilename($oldAvatar);
            }
        

            $this->crudManager->update($user);
            return $this->redirectToRoute('profile_index');
        }
        return $this->render('profile/edit.html.twig', ['user'=> $user, 'form' => $formProfile]);


    }

   
    #[Route('/password', name: 'password_edit', methods: ['GET', 'POST'])]
    public function editPassword(Request $request, PasswordManagerService $passwordManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            
            $passwordManager->upgradePassword($user, $newPassword);

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}