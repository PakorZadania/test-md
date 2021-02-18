<?php

use Service\TemplateBuilder;

class TemplateManager
{
    /** @var TemplateBuilder $templateBuilder */
    private $templateBuilder;

    /**
     * TemplateManager constructor.
     */
    public function __construct()
    {
        $this->templateBuilder = new TemplateBuilder();
    }

    /**
     * @param Template $tpl
     * @param array $data
     * @return Template
     */
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    /**
     * @param $text
     * @param array $data
     * @return string
     */
    private function computeText($text, array $data)
    {
        /** @var Quote|null $quote */
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        if ($quote) {
            $this->updateTemplateByQuote($quote);
        }

        $this->updateTemplateByUser($data);

        return $this->templateBuilder->execute($text);
    }

    /**
     * @param Quote $quote
     */
    private function updateTemplateByQuote(Quote $quote)
    {
        /** @var Quote $quoteFromRepository */
        $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        /** @var Site $site */
        $site = SiteRepository::getInstance()->getById($quote->siteId);
        /** @var Destination $destinationOfQuote */
        $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

        $destination = DestinationRepository::getInstance()->getById($quote->destinationId);

        $this->templateBuilder->setSummaryHtml(Quote::renderHtml($quoteFromRepository));
        $this->templateBuilder->setSummary(Quote::renderText($quoteFromRepository));
        $this->templateBuilder->setDestinationName($destinationOfQuote->countryName);

        $destinationLink = $site->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepository->id;
        $this->templateBuilder->setDestinationLink($destinationLink);
    }

    /**
     * @param array $data
     * @return void
     */
    private function updateTemplateByUser(array $data)
    {
        $applicationContext = ApplicationContext::getInstance();
        $user = (isset($data['user']) and ($data['user'] instanceof User)) ? $data['user'] : $applicationContext->getCurrentUser();

        if($user) {
            $this->templateBuilder->setFirstName($user->firstname);
        }
    }
}
