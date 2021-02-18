<?php

use Service\TemplateBuilder;

class TemplateManager
{
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

        /** @var User $user */
        $user = $this->getUser($data);
        if($user) {
            $templateBuilder->setFirstName($user->firstname);
        }

        return $templateBuilder->execute($text);
    }

    private function updateTextByQuote(Quote $quote, TemplateBuilder &$templateBuilder)
    {
        /** @var Quote $quoteFromRepository */
        $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        /** @var Site $site */
        $site = SiteRepository::getInstance()->getById($quote->siteId);
        /** @var Destination $destinationOfQuote */
        $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

        $destination = DestinationRepository::getInstance()->getById($quote->destinationId);

        $templateBuilder->setSummaryHtml(Quote::renderHtml($quoteFromRepository));
        $templateBuilder->setSummary(Quote::renderText($quoteFromRepository));
        $templateBuilder->setDestinationName($destinationOfQuote->countryName);

        $destinationLink = $site->url . '/' . $destination->countryName . '/quote/' . $quoteFromRepository->id;
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
}
