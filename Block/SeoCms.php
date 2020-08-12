<?php
declare(strict_types=1);

namespace Iop\SeoCms\Block;

use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
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
     * SeoCms constructor.
     * @param Template\Context $context
     * @param PageRepository $pageRepository
     * @param PageResource $resourcePage
     * @param StoreCollectionFactory $storeCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PageRepository $pageRepository,
        PageResource $resourcePage,
        StoreCollectionFactory $storeCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageRepository = $pageRepository;
        $this->resourcePage = $resourcePage;
        $this->storeCollectionFactory = $storeCollectionFactory;
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

        $pageId = $this->getRequest()->getParam('page_id', $this->getRequest()->getParam('id', false));
        if (!$pageId) {
            return null;
        }

        /** @var array $storeIds */
        $storeIds = $this->resourcePage->lookupStoreIds((int)$pageId);

        if (is_array($storeIds) && !count($storeIds)) {
            return null;
        }

        /** @var StoreCollectionFactory $storeCollection */
        $storeCollection = $this->storeCollectionFactory->create()
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addFieldToFilter('website_id', ['neq' => 0]);

        $storeCollection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['website_id'])
            ->group('website_id');

        /** data is prepared when websites by storesIds found more then 1 */
        if ($storeCollection->getSize() > 1) {
            /** @var string $pageIndentifier */
            $pageIndentifier = $this->getPageIdentifier($pageId);

            foreach ($storeIds as $storeId) {
                $store = $this->storeManager->getStore((int)$storeId);

                /** @var string $pageUrl */
                $pageUrl = $store->getBaseUrl() . $pageIndentifier;

                $data[] = [
                    'storeLanguage' => $this->getLangByStore((int)$storeId),
                    'pageUrl' => $pageUrl
                ];
            }
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
    public function getPageIdentifier($pageId): ?string
    {
        /** @var Magento\Cms\Model\PageRepository $page */
        $page = $this->pageRepository->getById($pageId);
        if (!$page->getId()) {
            return null;
        }
        return $page->getIdentifier();
    }
}
