<?php
namespace releasecheck;

/**
 * CAF specific Admin behaviour
 *
 * CAF admin is restrictive:
 *  - Remove CoCov2
 *  - Remove ESI
 */
class AdminCAF extends Admin
{
    /**
     * Setup CAF-specific admin behaviour
     */
    public function __construct()
    {
        parent::__construct();

        //  Remove CoCov2 completely
        unset($this->tests['CoCov2']);
    }

    /**
     * Override navigation tabs to remove ESI
     */
    public function showNavTabs($tab)
    {
        $idpParam = isset($_GET['idp'])
          ? '&amp;idp=' . urlencode($_GET['idp'])
          : '';

        printf('        <ul class="nav nav-tabs">%s', "\n");

        // Render only remaining EC tests
        foreach ($this->tests as $test => $data) {
            printf(
                '          <li class="nav-item">
                    <a class="nav-link%s" href="?tab=%s%s">%s</a>
                  </li>%s',
                $tab === $test ? self::HTML_ACTIVE : '',
                $test,
                $idpParam,
                $data['displayName'],
                "\n"
            );
        }

        // MFA is still allowed for CAF
        printf(
            '          <li class="nav-item">
                <a class="nav-link%s" href="?tab=mfa%s">MFA</a>
              </li>
            </ul>%s',
            $tab === 'mfa' ? self::HTML_ACTIVE : '',
            $idpParam,
            "\n"
        );
    }

    /**
     * Block access to removed tabs even via direct URL
     */
    public function showTab($tab)
    {
        if ($tab === 'CoCov2' || $tab === 'esi') {
            return;
        }

        parent::showTab($tab);
    }

    /**
     * Explicitly disable ESI handler
     */
    public function showESI()
    {
        return;
    }
}