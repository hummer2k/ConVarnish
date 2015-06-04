<?php

namespace ConVarnish\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class EsiUrl extends AbstractHelper
{
    /**
     *
     * @param string $blockId
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
