<?php

use Service\TemplateBuilder;

class TemplateManager
{
    /** @var TemplateBuilder $templateBuilder */

    private $quoteFromRepository;
    private $usefulObject;
    private $destination;

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

    private function computeText($text, array $data)
    {
        /** @var TemplateBuilder $templateBuilder */
        $templateBuilder = new TemplateBuilder();

        /** @var Quote|null $quote */
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        if ($quote) {
            $this->updateTextByQuote($quote, $templateBuilder);
        }

        $this->addDestinationLink($templateBuilder);

        /** @var User $user */
        $user = $this->getUser($data);
        if($user) {
            $templateBuilder->setFirstName($user->firstname);
        }

        return $templateBuilder->execute($text);
    }

    private function updateTextByQuote(Quote $quote, TemplateBuilder &$templateBuilder)
    {
        /** @var Quote $_quoteFromRepository */
        $this->quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        /** @var Site $usefulObject */
        $this->usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
        /** @var Destination $destinationOfQuote */
        $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

        $this->destination = DestinationRepository::getInstance()->getById($quote->destinationId);

        $templateBuilder->setSummaryHtml(Quote::renderHtml($this->quoteFromRepository));
        $templateBuilder->setSummary(Quote::renderText($this->quoteFromRepository));
        $templateBuilder->setDestinationName($destinationOfQuote->countryName);
    }

    private function addDestinationLink(TemplateBuilder &$templateBuilder)
    {
        $destinationLink = isset($this->destination) ? $this->usefulObject->url . '/' . $this->destination->countryName . '/quote/' . $this->quoteFromRepository->id : '';
        $templateBuilder->setDestinationLink($destinationLink);
    }

    /**
     * @param array $data
     * @return User
     */
    private function getUser(array $data)
    {
        $applicationContext = ApplicationContext::getInstance();
        return (isset($data['user']) and ($data['user'] instanceof User)) ? $data['user'] : $applicationContext->getCurrentUser();
    }

    private function addContentToTemplateTag(&$template, $tag, $content)
    {
        if (strpos($template, $tag) !== false) {
            $template = str_replace($tag, $content, $template);
        }
    }
}
