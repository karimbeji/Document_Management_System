<?php

namespace GedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use GedBundle\Entity\Folder;
use GedBundle\Utils\FieldDescription;
use GedBundle\Entity\File;

class SearchController extends Controller
{

    private $folderHeader = array();
    private $folderMappedFields = array();

    private $fileHeader = array();
    private $fileMappedFields = array();



    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    public function showAction()
    {
        return $this->render('GedBundle:CRUD:search.html.twig');
    }

    public function resultAction(Request $request)
    {
        if ( !$user = $this->get('security.token_storage')->getToken()->getUser())
        {
            throw $this->createAccessDeniedException(sprintf('You need to be authenticated to do search'));
        }

        $term = $request->request->get('term');

        $folderRepository = $this->getDoctrine()->getRepository('GedBundle:Folder');
        $fileRepository = $this->getDoctrine()->getRepository('GedBundle:File');

        $files = $fileRepository->searchByName($term);
        $folders = $folderRepository->searchByName($term);

        $this->addFolderHeader('Name', 'text');
        $this->addFolderHeader('Description', 'text');
        $this->addFolderHeader('Created at', 'datetime');
        $this->addFolderHeader('Updated at', 'datetime');
        $this->addFolderHeader('Created by', 'string');
        $this->addFolderHeader('Last updated by', 'string');

        $this->addFolderMappedField('name');
        $this->addFolderMappedField('description');
        $this->addFolderMappedField('created');
        $this->addFolderMappedField('updated');
        $this->addFolderMappedField('createdBy');
        $this->addFolderMappedField('updatedBy');

        $this->addFileHeader('Name', 'text');
        $this->addFileHeader('Description', 'text');
        $this->addFileHeader('Created at', 'datetime');
        $this->addFileHeader('Updated at', 'datetime');
        $this->addFileHeader('Created by', 'string');
        $this->addFileHeader('Last updated by', 'string');

        $this->addFileMappedField('name');
        $this->addFileMappedField('description');
        $this->addFileMappedField('created');
        $this->addFileMappedField('updated');
        $this->addFileMappedField('createdBy');
        $this->addFileMappedField('updatedBy');


        return $this->render(
            'GedBundle:CRUD:searchResult.html.twig',
            array(

            'term' => $term,

            'files' => $files,
            'folders' => $folders,

            'folder_headers' => $this->folderHeader,
            'folder_fields'  => $this->folderMappedFields,

            'file_headers'   => $this->fileHeader,
            'file_fields'    => $this->fileMappedFields,
            )
        );
    }

    public function searchAction(Request $request)
    {

        $form = $this->createFormBuilder()
                    ->add('input', TextType::class, array(
                        'attr' => array(
                            'class' => 'form-control',
                            'placeholder' => 'Search...',
                    )))
                    ->add('options', ChoiceType::class, array(
                        'attr' => array(
                            'class' => 'form-control',
                        ),
                        'choices' => array(
                            'Name' => 'Name',
                            'Tag' => 'Tag',
                            'Creators' => 'Creators',
//                            'Type' => 'Type',
//                            'Nature' => 'Nature',
                        )
                    ))
                    ->add('search', 'submit', array(
                        'attr' => array(
                            'class' => 'btn btn-primary'
                        )))
                    ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $term = $form->get('input')->getData();
            $choice = $form->get('options')->getData();

            $folders = array();
            $files  = array();

            switch( $choice )
            {
                case 'Name':
                    $this->searchByName($term, $files, $folders);
                    break;
                case 'Tag':
                    $files = $this->searchByTag($term);
                    break;
                case 'Creators':
                    $this->searchByCreators($term, $files, $folders);
                    break;
//                case 'Type':
//                case 'Nature':
            }


            $this->addFolderHeader('Name', 'text');
            $this->addFolderHeader('Description', 'text');
            $this->addFolderHeader('Created at', 'datetime');
            $this->addFolderHeader('Updated at', 'datetime');
            $this->addFolderHeader('Created by', 'string');
            $this->addFolderHeader('Last updated by', 'string');

            $this->addFolderMappedField('name');
            $this->addFolderMappedField('description');
            $this->addFolderMappedField('created');
            $this->addFolderMappedField('updated');
            $this->addFolderMappedField('createdBy');
            $this->addFolderMappedField('updatedBy');

            $this->addFileHeader('Name', 'text');
            $this->addFileHeader('Description', 'text');
            $this->addFileHeader('Created at', 'datetime');
            $this->addFileHeader('Updated at', 'datetime');
            $this->addFileHeader('Created by', 'string');
            $this->addFileHeader('Last updated by', 'string');

            $this->addFileMappedField('name');
            $this->addFileMappedField('description');
            $this->addFileMappedField('created');
            $this->addFileMappedField('updated');
            $this->addFileMappedField('createdBy');
            $this->addFileMappedField('updatedBy');

            dump( $term );

            return $this->render(
                'GedBundle:CRUD:searchResult.html.twig',
                array(

                    'term' => $term,

                    'files' => $files,
                    'folders' => $folders,

                    'folder_headers' => $this->folderHeader,
                    'folder_fields'  => $this->folderMappedFields,

                    'file_headers'   => $this->fileHeader,
                    'file_fields'    => $this->fileMappedFields,
                )
            );

        }

        return $this->render(
            'GedBundle:CRUD:search.html.twig',
            array(
                'form' => $form->createView(),
            )
        );

    }

    public function searchByNameAction(Request $request)
    {
        if ( !$user = $this->get('security.token_storage')->getToken()->getUser())
        {
            throw $this->createAccessDeniedException(sprintf('You need to be authenticated to do a search'));
        }

        $name = $request->get("name");

        $folderRepository = $this->getDoctrine()->getRepository('GedBundle:Folder');
        $fileRepository = $this->getDoctrine()->getRepository('GedBundle:File');

        $files = $fileRepository->searchByName($name);
        $folders = $folderRepository->searchByName($name);

        $response = new JsonResponse();
        return $response->setData(array('status' => 'success', 'files' => $files, 'folders' => $folders ));

    }

    public function searchByTag($term)
    {
        if ( !$user = $this->get('security.token_storage')->getToken()->getUser())
        {
            throw $this->createAccessDeniedException(sprintf('You need to be authenticated to do a search'));
        }

        $fileRepository = $this->getDoctrine()->getRepository('GedBundle:File');

        $files = $fileRepository->searchFileByTag($term);

        return $files;
    }

    public function searchByCreators($term, &$files, &$folders)
    {
        if ( !$user = $this->get('security.token_storage')->getToken()->getUser())
        {
            throw $this->createAccessDeniedException(sprintf('You need to be authenticated to do a search'));
        }


        $folderRepository = $this->getDoctrine()->getRepository('GedBundle:Folder');
        $fileRepository = $this->getDoctrine()->getRepository('GedBundle:File');

        $files = $fileRepository->searchFileByUser($term);
        $folders = $folderRepository->searchFolderByUser($user);

    }

    public function searchByName($term, &$files, &$folders)
    {
        if ( !$user = $this->get('security.token_storage')->getToken()->getUser())
        {
            throw $this->createAccessDeniedException(sprintf('You need to be authenticated to do a search'));
        }


        $folderRepository = $this->getDoctrine()->getRepository('GedBundle:Folder');
        $fileRepository = $this->getDoctrine()->getRepository('GedBundle:File');

        $files = $fileRepository->searchByName($term);
        $folders = $folderRepository->searchByName($term);

    }

    public function searchByTypeAction()
    {

    }

    public function searchByNatureAction()
    {

    }

    /**
     * @return array
     */
    public function getFolderHeader()
    {
        return $this->folderHeader;
    }

    /**
     * @param array $folderHeader
     */
    public function setFolderHeader($folderHeader)
    {
        $this->folderHeader = $folderHeader;
    }

    /**
     * @return array
     */
    public function getFolderMappedFields()
    {
        return $this->folderMappedFields;
    }

    /**
     * @param array $folderMappedFields
     */
    public function setFolderMappedFields($folderMappedFields)
    {
        $this->folderMappedFields = $folderMappedFields;
    }

    /**
     * @return array
     */
    public function getFileHeader()
    {
        return $this->fileHeader;
    }

    /**
     * @param array $fileHeader
     */
    public function setFileHeader($fileHeader)
    {
        $this->fileHeader = $fileHeader;
    }

    /**
     * @return array
     */
    public function getFileMappedFields()
    {
        return $this->fileMappedFields;
    }

    /**
     * @param array $fileMappedFields
     */
    public function setFileMappedFields($fileMappedFields)
    {
        $this->fileMappedFields = $fileMappedFields;
    }

    public function addFolderHeader($label, $type)
    {
        $folderDesc = new FieldDescription($label, $type);
        $this->folderHeader[] = $folderDesc;
    }

    public function addFolderMappedField($field)
    {
        $this->folderMappedFields[] = $field;
    }

    public function addFileHeader($label, $type)
    {
        $fileDesc = new FieldDescription($label, $type);
        $this->fileHeader[] = $fileDesc;
    }

    public function addFileMappedField($field)
    {
        $this->fileMappedFields[] = $field;
    }

}
