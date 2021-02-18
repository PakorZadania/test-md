<?php

class TemplateManager
{
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
        /** @var Quote|null $quote */
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        if ($quote) {
            $this->updateTextByQuote($quote, $text);
        }

        $this->addDestinationLink($text);

        /** @var User $user */
        $user = $this->getUser($data);
        if($user) {
            $this->addContentToTemplateTag($text, '[user:first_name]', ucfirst(mb_strtolower($user->firstname)));
        }

        return $text;
    }

    private function updateTextByQuote(Quote $quote, &$text)
    {
        /** @var Quote $_quoteFromRepository */
        $this->quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        /** @var Site $usefulObject */
        $this->usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
        /** @var Destination $destinationOfQuote */
        $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

        $this->destination = DestinationRepository::getInstance()->getById($quote->destinationId);

        $this->addContentToTemplateTag($text, '[quote:summary_html]', Quote::renderHtml($this->quoteFromRepository));
        $this->addContentToTemplateTag($text, '[quote:summary]', Quote::renderText($this->quoteFromRepository));
        $this->addContentToTemplateTag($text, '[quote:destination_name]', $destinationOfQuote->countryName);
    }

    private function addDestinationLink(&$text)
    {
        $destinationLink = isset($this->destination) ? $this->usefulObject->url . '/' . $this->destination->countryName . '/quote/' . $this->quoteFromRepository->id : '';
        $this->addContentToTemplateTag($text, '[quote:destination_link]', $destinationLink);
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
