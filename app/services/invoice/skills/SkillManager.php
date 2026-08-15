<?php
require_once __DIR__ . '/InvoiceSkillInterface.php';
require_once __DIR__ . '/GeneralInvoiceSkill.php';
require_once __DIR__ . '/MdrInvoiceSkill.php';
require_once __DIR__ . '/BudiJayaInvoiceSkill.php';

/**
 * SkillManager
 *
 * Registry and manager for all supplier-specific invoice AI skills.
 *
 * @package AlfarezMart\Services\Invoice\Skills
 */
class SkillManager
{
    /** @var InvoiceSkillInterface[] */
    private $skills = [];

    public function __construct()
    {
        $this->registerSkill(new GeneralInvoiceSkill());
        $this->registerSkill(new MdrInvoiceSkill());
        $this->registerSkill(new BudiJayaInvoiceSkill());
    }

    public function registerSkill(InvoiceSkillInterface $skill): void
    {
        $this->skills[$skill->getSkillKey()] = $skill;
    }

    /**
     * Get skill by key, fallback to GeneralInvoiceSkill if not found.
     */
    public function getSkill(string $key): InvoiceSkillInterface
    {
        return $this->skills[$key] ?? $this->skills['general'];
    }

    /**
     * Match text signatures against all registered skills.
     */
    public function findSkillBySignatures(string $text): InvoiceSkillInterface
    {
        $textLower = strtolower($text);

        foreach ($this->skills as $key => $skill) {
            if ($key === 'general') continue; // skip fallback in signature match

            foreach ($skill->getDetectionSignatures() as $sig) {
                if (!empty($sig) && strpos($textLower, strtolower($sig)) !== false) {
                    return $skill;
                }
            }
        }

        return $this->skills['general'];
    }

    /**
     * @return InvoiceSkillInterface[]
     */
    public function getAllSkills(): array
    {
        return $this->skills;
    }
}
