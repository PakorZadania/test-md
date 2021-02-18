<?php

class TemplateManager
{
    private $_quoteFromRepository;
    private $usefulObject;

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
        /**
         * @var Quote|null $quote
         */
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote) {
            $this->updateTextByQuote($quote, $text);

            if(strpos($text, '[quote:destination_link]') !== false){
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            }
        }

        if (isset($destination))
            $this->addContentToTemplateTag($text, '[quote:destination_link]', $this->usefulObject->url . '/' . $destination->countryName . '/quote/' . $this->_quoteFromRepository->id);
        else
            $this->addContentToTemplateTag($text, '[quote:destination_link]', '');


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
        $this->_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        /** @var Site $usefulObject */
        $this->usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
        /** @var  Destination $destinationOfQuote */
        $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

        $this->addContentToTemplateTag($text, '[quote:summary_html]', Quote::renderHtml($this->_quoteFromRepository));
        $this->addContentToTemplateTag($text, '[quote:summary]', Quote::renderText($this->_quoteFromRepository));
        $this->addContentToTemplateTag($text, '[quote:destination_name]', $destinationOfQuote->countryName);
    }

    /**
     * @param array $data
     * @return User
     */
    private function getUser(array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();
        return (isset($data['user']) and ($data['user'] instanceof User)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
    }

    private function addContentToTemplateTag(&$template, $tag, $content)
    {
        if (strpos($template, $tag) !== false) {
            $template = str_replace($tag, $content, $template);
        }
    }
}
