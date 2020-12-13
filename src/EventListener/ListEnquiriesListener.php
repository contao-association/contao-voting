<?php

declare(strict_types=1);

namespace ContaoAssociation\VotingBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Doctrine\DBAL\Connection;

/**
 * @Callback(table="tl_voting_enquiry", target="list.sorting.child_record")
 */
class ListEnquiriesListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(array $arrRow): string
    {
        static $total;

        if (null === $total) {
            $total = $this->connection->fetchOne(
                "SELECT COUNT(*) AS total FROM tl_voting_registry WHERE voting=?",
                [$arrRow['pid']]
            );
        }

        return '<div>
<h4>' . $arrRow['name'] . '</h4>
' . $arrRow['teaser'] . '
<table class="tl_listing" style="width:50%;">
    <tbody>
        <tr>
            <td class="tl_folder_tlist" colspan="2">' . $GLOBALS['TL_LANG']['MSC']['voting_summary'] . '</td>
        </tr>
        <tr>
            <td class="tl_file_list tl_green">' . $GLOBALS['TL_LANG']['MSC']['voting_options']['yes'] . '</td>
            <td class="tl_file_list tl_green">' . $arrRow['ayes'] . '</td>
        </tr>
        <tr>
            <td class="tl_file_list tl_red">' . $GLOBALS['TL_LANG']['MSC']['voting_options']['no'] . '</td>
            <td class="tl_file_list tl_red">' . $arrRow['nays'] . '</td>
        </tr>
        <tr>
            <td class="tl_file_list"><strong>' . $GLOBALS['TL_LANG']['MSC']['voting_total'] . '</td>
            <td class="tl_file_list"><strong>' . ($arrRow['ayes'] + $arrRow['nays']) . '</td>
        </tr>
        <tr>
            <td class="tl_file_list"><strong>' . $GLOBALS['TL_LANG']['MSC']['voting_participants'] . '</td>
            <td class="tl_file_list"><strong>' . $total . '</td>
        </tr>
    </tbody>
</table>
</div>' . "\n";
    }
}
