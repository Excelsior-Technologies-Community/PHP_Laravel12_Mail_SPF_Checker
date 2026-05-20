<?php

namespace App\Services;

class SPFCheckerService
{
    protected string $mailerHost = 'smtp.gmail.com';

    public function using(string $mailserver): self
    {
        $this->mailerHost = $mailserver ?: 'smtp.gmail.com';

        return $this;
    }

    public function canISendAs(string $email): bool
    {
        if (empty($this->mailerHost)) {
            return false;
        }

        $domain = $this->extractDomain($email);

        if ($this->isGoogleDomain($domain)) {
            return false;
        }

        $spfRecord = @dns_get_record($domain, DNS_TXT);

        if (!$spfRecord) {
            return false;
        }

        $cleanHost = str_replace('smtp.', '', strtolower($this->mailerHost));

        foreach ($spfRecord as $txt) {
            if (isset($txt['txt'])) {
                $record = strtolower($txt['txt']);

                if (
                    stripos($record, strtolower($this->mailerHost)) !== false ||
                    stripos($record, $cleanHost) !== false
                ) {
                    return true;
                }
            }
        }

        return false;
    }

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