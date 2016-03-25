<?php
/**
 * @codeCoverageIgnore
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConVarnish\View\Helper;

use Zend\View\Helper\AbstractHelper;

class EsiUrl extends AbstractHelper
{
    /**
     *
     * @param string $blockId
     * @param array $handles
     * @return array|string
     */
    public function __invoke($blockId, array $handles = [])
    {
        $options = [];
        if (count($handles)) {
            $options = [
                'query' => [
                    'handles' => $handles
                ]
            ];
        }
        $url = $this->getView()->url(
            'esi',
            ['block' => $blockId],
            $options
        );
        return $url;
    }
}
