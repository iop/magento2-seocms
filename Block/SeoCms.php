<?php
declare(strict_types=1);

namespace Iop\SeoCms\Block;

use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SeoCms
 * @package Iop\SeoCms\Block
 */
class SeoCms extends Template
{
    /**
     * @var PageResource
     */
    protected $resourcePage;
    /**
     * @var PageRepository
     */
    protected $pageRepository;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var StoreCollectionFactory
     */
    protected $storeCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * SeoCms constructor.
     * @param Template\Context $context
     * @param PageRepository $pageRepository
     * @param PageResource $resourcePage
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PageRepository $pageRepository,
        PageResource $resourcePage,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageRepository = $pageRepository;
        $this->resourcePage = $resourcePage;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLinksData(): ?array
    {
        /** @var array $data */
        $data = [];

        $pageId = (int)$this->getRequest()->getParam('page_id', $this->getRequest()->getParam('id', 0));
        if (!$pageId) {
            return null;
        }

        /** @var string $pageIndentifier */
        $pageIndentifier = $this->getPageIdentifier($pageId);

        /** @var array $storeIds */
        $storeIds = $this->resourcePage->lookupStoreIds($pageId);

        /** links data is prepared when storesIds qty more then 1 */
        if (is_array($storeIds) && count($storeIds) > 1 && !empty($pageIndentifier)) {

            foreach ($storeIds as $storeId) {
                $store = $this->storeManager->getStore((int)$storeId);

                /** TODO: check is unique websiteID  */

                /** @var string $pageUrl */
                $pageUrl = $store->getBaseUrl() . $pageIndentifier;

                $data[] = [
                    'storeLanguage' => $this->getLangByStore((int)$storeId),
                    'pageUrl' => $pageUrl
                ];
            }
        } else {
            return null;
        }

        return $data;
    }

    /**
     * @param int $storeId
     * @return string
     */
    protected function getLangByStore(int $storeId): string
    {
        return $this->scopeConfig->getValue(
            'general/locale/seo_language_code',
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?? $this->getDefaultLangByStore($storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    protected function getDefaultLangByStore(int $storeId): string
    {
        return $this->scopeConfig->getValue(
            'general/locale/code',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $pageId
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPageIdentifier(int $pageId): ?string
    {
        try {
            return $this->pageRepository->getById($pageId)->getIdentifier();
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }
}
