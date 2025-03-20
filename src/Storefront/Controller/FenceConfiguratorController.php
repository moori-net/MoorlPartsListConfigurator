<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Storefront\Controller;

use MoorlForms\Core\Content\Form\FormCollection;
use MoorlForms\Core\Content\Form\FormEntity;
use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FenceConfiguratorController extends StorefrontController
{
    public function __construct(
        private readonly StringTemplateRenderer $templateRenderer
    )
    {
    }

    #[Route(path: '/moorl-fb-form/context-value/{elementId}', name: 'frontend.moorl-fb-form.context-value', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function contextValue(string $elementId, Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $value = "";

        if ($salesChannelContext->getCustomer()) {
            $element = $this->fbService->getElement($elementId, $salesChannelContext->getContext());

            try {
                $value = ($this->templateRenderer->render(
                    $element->getTranslation('defaultValue'),
                    ['context' => $salesChannelContext],
                    $salesChannelContext->getContext()
                ));
            } catch (\Throwable $exception) {

            }
        }

        return new Response($value);
    }

    #[Route(path: '/moorl-fb-form/modal', name: 'frontend.moorl-fb-form.modal', methods: ['GET', 'POST'], defaults: ['XmlHttpRequest' => true])]
    public function modal(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $form = $this->fbService->initForm($request->query->get('formId'), $salesChannelContext);

        return $this->renderStorefront('@MoorlForms/plugin/moorl-fb/component/form/form-modal-content.html.twig', [
            'form' => $form
        ]);
    }

    #[Route(path: '/moorl-fb-form/submit', name: 'frontend.moorl-fb-form.submit', methods: ['POST'], defaults: ['XmlHttpRequest' => true])]
    public function submit(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $formSubmitResponse = $this->fbService->submit($request, $salesChannelContext);

        if ($formSubmitResponse->isSuccess()) {
            $form = $formSubmitResponse->getForm();

            if ($form->getRelatedEntity() && $form->getInsertDatabase()) {
                $this->fbService->upsertEntity(new FormCollection([$form]), $salesChannelContext);
            }

            $response = [
                'success' => $formSubmitResponse->isSuccess(),
                'feedback' => $this->renderView(
                    '@MoorlForms/plugin/moorl-fb/component/feedback-messages.html.twig',
                    $formSubmitResponse->jsonSerialize()
                ),
                'location' => $this->getRedirectUrl($request, $form),
            ];
        } else {
            $response = [
                'success' => $formSubmitResponse->isSuccess(),
                'feedback' => $this->renderView(
                    '@MoorlForms/plugin/moorl-fb/component/feedback-messages.html.twig',
                    $formSubmitResponse->jsonSerialize()
                )
            ];
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($response);
        } else {
            return $this->renderStorefront(
                '@MoorlForms/plugin/moorl-fb/page/feedback.html.twig',
                [
                    'context' => $salesChannelContext,
                    'response' => $response
                ]
            );
        }
    }

    #[Route(path: '/moorl-fb-form/autocomplete', name: 'frontend.moorl-fb-form.autocomplete', methods: ['POST'], defaults: ['XmlHttpRequest' => true])]
    public function autocomplete(string $formId, string $formElementId, SalesChannelContext $context): JsonResponse
    {
        return new JsonResponse([]);
    }

    private function getRedirectUrl(Request $request, FormEntity $form): ?string
    {
        $config = $form->getConfig();

        if ($config && !empty($config['redirectType'])) {
            $request->request->set('redirectTo', $config['redirectType']);

            switch ($config['redirectType']) {
                case "url":
                    return $config['redirectUrl'];
                case "frontend.detail.page":
                    $request->request->set('redirectParameters', ['productId' => $config['redirectId']]);
                    break;
                case "frontend.navigation.page":
                    $request->request->set('redirectParameters', ['navigationId' => $config['redirectId']]);
                    break;
                default:
                    return "reload";
            }

            $response = $this->createActionResponse($request);

            return $response->getTargetUrl();
        }

        return null;
    }
}
