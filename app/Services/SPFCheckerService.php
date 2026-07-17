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
        return in_array($this->getGrade($email), ['Pass']);
    }

    /**
     * Returns: Pass | SoftFail | HardFail | Neutral | None
     */
    public function getGrade(string $email): string
    {
        $domain = $this->extractDomain($email);

        if ($this->isGoogleDomain($domain)) {
            return 'HardFail';
        }

        $records = @dns_get_record($domain, DNS_TXT);
        if (!$records) return 'None';

        $cleanHost = str_replace('smtp.', '', strtolower($this->mailerHost));
        $spfFound   = false;

        foreach ($records as $txt) {
            if (!isset($txt['txt'])) continue;
            $record = strtolower($txt['txt']);

            if (!str_starts_with($record, 'v=spf1')) continue;
            $spfFound = true;

            // Pass
            if (
                stripos($record, strtolower($this->mailerHost)) !== false ||
                stripos($record, $cleanHost) !== false
            ) {
                return 'Pass';
            }

            // Qualifier at end
            if (str_contains($record, '~all')) return 'SoftFail';
            if (str_contains($record, '-all')) return 'HardFail';
            if (str_contains($record, '?all')) return 'Neutral';
        }

        return $spfFound ? 'Neutral' : 'None';
    }

    /**
     * Deliverability score contribution from SPF (0-40)
     */
    public function scoreContribution(string $email): int
    {
        return match ($this->getGrade($email)) {
            'Pass'     => 40,
            'Neutral'  => 20,
            'SoftFail' => 10,
            default    => 0,
        };
    }

    public function howCanISendAs(string $email): string
    {
        $domain = $this->extractDomain($email);
        if ($this->isGoogleDomain($domain)) {
            return 'You cannot modify SPF for Gmail addresses. Use Gmail SMTP with authentication.';
        }
        return "Generate a TXT record for {$domain} with value: v=spf1 include:{$this->mailerHost} -all";
    }

    public function extractDomain(string $email): string
    {
        return substr(strrchr($email, '@'), 1);
    }

    protected function isGoogleDomain(string $domain): bool
    {
        return in_array(strtolower($domain), ['gmail.com', 'googlemail.com']);
    }
}