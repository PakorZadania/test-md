<?php

namespace Service;

class TemplateBuilder
{

    private $template;
    private $summaryHtml;
    private $summary;
    private $destinationName;
    private $destinationLink;
    private $firstName;

    /**
     * @param mixed $summaryHtml
     */
    public function setSummaryHtml($summaryHtml)
    {
        $this->summaryHtml = $summaryHtml;
    }

    /**
     * @param mixed $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @param mixed $destinationName
     */
    public function setDestinationName($destinationName)
    {
        $this->destinationName = $destinationName;
    }

    /**
     * @param mixed $destinationLink
     */
    public function setDestinationLink($destinationLink)
    {
        $this->destinationLink = $destinationLink;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = ucfirst(mb_strtolower($firstName));
    }

    private function addSummaryHtml()
    {
        $content = $this->summaryHtml !== null ? $this->summaryHtml : '';
        $this->addContentToTemplateTag('[quote:summary_html]', $content);
    }

    private function addSummary()
    {
        $content = $this->summary !== null ? $this->summary : '';
        $this->addContentToTemplateTag('[quote:summary]', $content);
    }

    private function addDestinationName()
    {
        $content = $this->destinationName !== null ? $this->destinationName : '';
        $this->addContentToTemplateTag('[quote:destination_name]', $content);
    }

    private function addDestinationLink()
    {
        $content = $this->destinationLink !== null ? $this->destinationLink : '';
        $this->addContentToTemplateTag('[quote:destination_link]', $content);
    }

    private function addFirstName()
    {
        $content = $this->firstName !== null ? $this->firstName : '';
        $this->addContentToTemplateTag('[user:first_name]', $content);
    }

    /**
     * @param string $template
     * @return string
     */
    public function execute($template)
    {
        $this->template = $template;

        $this->addSummaryHtml();
        $this->addSummary();
        $this->addDestinationName();
        $this->addDestinationLink();
        $this->addFirstName();

        return $this->template;
    }

    private function addContentToTemplateTag($tag, $content)
    {
        if (strpos($this->template, $tag) !== false) {
            $this->template = str_replace($tag, $content, $this->template);
        }
    }
}