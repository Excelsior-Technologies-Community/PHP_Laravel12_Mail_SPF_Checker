<?php

namespace App\Services;

class SPFCheckerService
{
    protected string $mailerHost = 'smtp.gmail.com'; // default

    public function using(string $mailserver): self
    {
        $this->mailerHost = $mailserver ?: 'smtp.gmail.com';
        return $this;
    }

    /**
     * Check if email can be sent via the specified mail server
     */
    public function canISendAs(string $email): bool
    {
        if (empty($this->mailerHost)) {
            return false; // ensure bool is always returned
        }

        $domain = $this->extractDomain($email);

        // Gmail/Google Workspace
        if ($this->isGoogleDomain($domain)) {
            return false;
        }

        // Normal SPF check
        $spfRecord = dns_get_record($domain, DNS_TXT) ?: [];

        foreach ($spfRecord as $txt) {
            if (isset($txt['txt']) && stripos($txt['txt'], $this->mailerHost) !== false) {
                return true;
            }
        }

        return false; // default
    }

    /**
     * Give suggestions on how to send
     */
    public function howCanISendAs(string $email): string
    {
        $domain = $this->extractDomain($email);

        if ($this->isGoogleDomain($domain)) {
            return "You cannot modify SPF for Gmail addresses. Use Gmail SMTP with authentication.";
        }

        return "Generate a TXT record for {$domain} with value: v=spf1 include:{$this->mailerHost} -all";
    }

    protected function extractDomain(string $email): string
    {
        return substr(strrchr($email, "@"), 1);
    }

    protected function isGoogleDomain(string $domain): bool
    {
        return in_array(strtolower($domain), ['gmail.com', 'googlemail.com']);
    }
}