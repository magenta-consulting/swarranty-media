<?php

namespace Magenta\Bundle\SWarrantyMediaApiBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\View\View as FOSRestView;
use Magenta\Bundle\SWarrantyModelBundle\Entity\Customer\ServiceSheet;
use Magenta\Bundle\SWarrantyModelBundle\Entity\Customer\Warranty;
use Magenta\Bundle\SWarrantyModelBundle\Entity\Media\Media;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\MediaBundle\Controller\Api\MediaController as SonataMediaController;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Form\Type\ApiMediaType;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaController extends SonataMediaController
{
    /** @var RegistryInterface */
    private $registry;

    /**
     * @param int $id
     *                We should report this
     *
     * @return View|mixed
     */
    public function deleteMediumAction($id)
    {
        $data = parent::deleteMediumAction($id);
        $view = FOSRestView::create($data);

        return $view;
    }

    private function populateOwnerFields(Media $media, array $fields, Request $request)
    {
        foreach ($fields as $field => $class) {
            $id = $request->get($field);
            if (!empty($id)) {
                $repo = $this->registry->getRepository($class);
                if (!empty($owner = $repo->find($id))) {
                    $media->{'set'.ucfirst($field)}($owner);

                    return $owner;
                }
            }
        }
    }

    /**
     * Write a medium, this method is used by both POST and PUT action methods.
     *
     * @return View|FormInterface
     */
    protected function handleWriteMedium(Request $request, MediaInterface $media, MediaProviderInterface $provider)
    {
        // sonata-project\media-bundle\src\Resources\config\api_form_doctrine_orm.xml
        $form = $this->formFactory->createNamed(null, ApiMediaType::class, $media, [
            'provider_name' => $provider->getName(),
            'csrf_protection' => false,
        ]);

        $token = $request->get('token');

        $form->handleRequest($request);

//        if ($form->isValid()) {
        /** @var Media $media */
        $media = $form->getData();
        if ($media instanceof MediaInterface) {
            if (!empty($mc = $request->query->get('context'))) {
                $media->setContext($mc);
            }

            $this->populateOwnerFields($media, [
                'receiptImageWarranty' => Warranty::class,
                'imageServiceSheet' => ServiceSheet::class,
            ], $request);

            $media->setToken($token);

            var_dump(empty($media->getBinaryContent()));
            /** @var UploadedFile $uploaded */
            $uploaded = $media->getBinaryContent();

            if (empty($media->getWidth()) || empty($media->getHeight())) {
                $mediaPath = $uploaded->getRealPath();
                $mimeType = $uploaded->getMimeType();

                if (false !== strpos($mimeType, 'image')) {
                    list($width, $height, $type, $attr) = getimagesize($mediaPath);
                    $media->setWidth($width);
                    $media->setHeight($height);
                }
            }

            $this->mediaManager->save($media);

            $context = new Context();
            $context->setGroups(['read_medium', 'sonata_api_read']);
            $context->enableMaxDepth();

            $view = FOSRestView::create($media);
            $view->setContext($context);

            return $view;
        }

        $errors = $form->getErrors(true, true);
        $errorMsg = '';
        /** @var FormError $error */
        foreach ($errors as $error) {
            $errorMsg .= $error->getMessage().' Caused by: '.$error->getCause().'  data class: '.get_class($form->getData());
        }

        return new JsonResponse($errorMsg);

        //		return $form;
    }

    /**
     * Returns media binary content for the format (reference by default).
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="media id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @param $id
     * @param $format
     *
     * @return Response
     */
    public function getMediumBinaryViewAction(Request $request, $id, $format = MediaProviderInterface::FORMAT_REFERENCE)
    {
        /** @var Media $media */
        $media = $this->getMedium($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        $token = $request->get('token');
        if ($warranty = $media->getReceiptImageWarranty()) {
            if ($org = $warranty->getOrganisation()) {
                if ($token !== $org->getSystem()->getAdminToken() && $token !== $media->getToken() && !empty($token)) {
//                    if ($warranty->getRegistration()->isEmailSent()) {
                        throw new AccessDeniedException();
//                    }
                }
            }
        }

        if (!$this->mediaPool->getDownloadStrategy($media)->isGranted($media, $request)) {
            throw new AccessDeniedException();
        }

        $response = $this->getViewBinaryResponse($media, $format, $this->mediaPool->getDownloadMode($media));

        if ($response instanceof BinaryFileResponse) {
            $response->prepare($request);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewBinaryResponse(MediaInterface $media, $format, $mode, array $headers = [])
    {
        $provider = $this->getProvider($media);

        // build the default headers
        $headers = array_merge([
            'Content-Type' => $media->getContentType(),
//			'Content-Disposition' => sprintf('attachment; filename="%s"', $media->getMetadataValue('filename')),
            'Content-Disposition' => sprintf('inline; filename="%s"', $media->getMetadataValue('filename')),
        ], $headers);

        if (!in_array($mode, ['http', 'X-Sendfile', 'X-Accel-Redirect'])) {
            throw new \RuntimeException('Invalid mode provided');
        }

        if ('http' == $mode) {
            if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
                $file = $provider->getReferenceFile($media);
            } else {
                $file = $provider->getFilesystem()->get($provider->generatePrivateUrl($media, $format));
            }

            return new StreamedResponse(function () use ($file) {
                echo $file->getContent();
            }, 200, $headers);
        }

        if (!$provider->getFilesystem()->getAdapter() instanceof Local) {
            throw new \RuntimeException('Cannot use X-Sendfile or X-Accel-Redirect with non \Sonata\MediaBundle\Filesystem\Local');
        }

        $filename = sprintf('%s/%s',
            $provider->getFilesystem()->getAdapter()->getDirectory(),
            $provider->generatePrivateUrl($media, $format)
        );

        return new BinaryFileResponse($filename, 200, $headers);
    }

    /**
     * Returns media urls for each format.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="media id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @param $id
     *
     * @return array
     */
    public function getMediumFormatsAction($id)
    {
        $properties = parent::getMediumFormatsAction($id);
        $properties['private-url'] = sprintf('/media-api/media/%s/binaries/reference/view.json', $id);

        return $properties;
    }

    /**
     * @return MediaProviderInterface
     */
    public function getProvider(MediaInterface $media)
    {
        return $this->mediaPool->getProvider($media->getProviderName());
    }

    public function setRegistry(RegistryInterface $registry): void
    {
        $this->registry = $registry;
    }
}
