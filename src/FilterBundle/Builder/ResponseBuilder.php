<?php

namespace BestIt\Commercetools\FilterBundle\Builder;

use BestIt\Commercetools\FilterBundle\Factory\FacetCollectionFactory;
use BestIt\Commercetools\FilterBundle\Factory\PaginationFactory;
use BestIt\Commercetools\FilterBundle\Form\FacetType;
use BestIt\Commercetools\FilterBundle\Model\Context;
use BestIt\Commercetools\FilterBundle\Normalizer\ProductNormalizerInterface;
use BestIt\Commercetools\FilterBundle\Model\Response;
use Commercetools\Core\Response\PagedSearchResponse;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Builder for parsing response
 * @author chowanski <chowanski@bestit-online.de>
 * @package BestIt\Commercetools\FilterBundle
 * @subpackage Builder
 */
class ResponseBuilder
{
    /**
     * Factory for pagination
     * @var PaginationFactory
     */
    private $paginationFactory;

    /**
     * Form factory
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * The facet collection factory
     * @var FacetCollectionFactory
     */
    private $facetCollectionFactory;

    /**
     * The product normalizer
     * @var ProductNormalizerInterface
     */
    private $productNormalizer;

    /**
     * ResponseManager constructor.
     * @param ProductNormalizerInterface $productNormalizer
     * @param PaginationFactory $paginationFactory
     * @param FormFactoryInterface $formFactory
     * @param FacetCollectionFactory $facetCollectionFactory
     */
    public function __construct(
        ProductNormalizerInterface $productNormalizer,
        PaginationFactory $paginationFactory,
        FormFactoryInterface $formFactory,
        FacetCollectionFactory $facetCollectionFactory
    ) {
        $this
            ->setProductNormalizer($productNormalizer)
            ->setPaginationFactory($paginationFactory)
            ->setFormFactory($formFactory)
            ->setFacetCollectionFactory($facetCollectionFactory);
    }

    /**
     * Build response
     * @param Context $context
     * @param PagedSearchResponse $pagedSearchResponse
     * @return Response
     */
    public function build(Context $context, PagedSearchResponse $pagedSearchResponse): Response
    {
        $totalProducts = $pagedSearchResponse->getTotal();
        $facets = $pagedSearchResponse->getFacets();

        $pagination = $this->getPaginationFactory()->create($context, $totalProducts);
        $facetCollection = $this->getFacetCollectionFactory()->create($facets);

        $form = $this->getFormFactory()->create(FacetType::class, [], [
            'facets' => $facetCollection,
            'method' => 'GET'
        ]);

        $form->submit($context->getQuery()['facet'] ?? []);

        $products = [];
        foreach ($pagedSearchResponse->toObject() as $product) {
            $products[] = $this->getProductNormalizer()->normalize($product);
        }

        $response = (new Response())
            ->setContext($context)
            ->setProducts($products)
            ->setTotalProducts($totalProducts)
            ->setPagination($pagination)
            ->setForm($form->createView());

        return $response;
    }

    /**
     * Get productNormalizer
     * @return ProductNormalizerInterface
     */
    private function getProductNormalizer(): ProductNormalizerInterface
    {
        return $this->productNormalizer;
    }

    /**
     * Set productNormalizer
     * @param ProductNormalizerInterface $productNormalizer
     * @return ResponseBuilder
     */
    private function setProductNormalizer(ProductNormalizerInterface $productNormalizer): ResponseBuilder
    {
        $this->productNormalizer = $productNormalizer;
        return $this;
    }

    /**
     * Get facetCollectionFactory
     * @return FacetCollectionFactory
     */
    private function getFacetCollectionFactory(): FacetCollectionFactory
    {
        return $this->facetCollectionFactory;
    }

    /**
     * Get formFactory
     * @return FormFactoryInterface
     */
    private function getFormFactory(): FormFactoryInterface
    {
        return $this->formFactory;
    }

    /**
     * Get paginationFactory
     * @return PaginationFactory
     */
    private function getPaginationFactory(): PaginationFactory
    {
        return $this->paginationFactory;
    }

    /**
     * Set facetCollectionFactory
     * @param FacetCollectionFactory $facetCollectionFactory
     * @return ResponseBuilder
     */
    private function setFacetCollectionFactory(FacetCollectionFactory $facetCollectionFactory): ResponseBuilder
    {
        $this->facetCollectionFactory = $facetCollectionFactory;

        return $this;
    }

    /**
     * Set formFactory
     * @param FormFactoryInterface $formFactory
     * @return ResponseBuilder
     */
    private function setFormFactory(FormFactoryInterface $formFactory): ResponseBuilder
    {
        $this->formFactory = $formFactory;

        return $this;
    }

    /**
     * Set paginationFactory
     * @param PaginationFactory $paginationFactory
     * @return ResponseBuilder
     */
    private function setPaginationFactory(PaginationFactory $paginationFactory): ResponseBuilder
    {
        $this->paginationFactory = $paginationFactory;

        return $this;
    }
}