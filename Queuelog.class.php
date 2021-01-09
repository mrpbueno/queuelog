<?php


namespace FreePBX\modules;


use Exception;
use FreePBX\BMO;
use FreePBX\FreePBX_Helpers;
use FreePBX\modules\Queuelog\Traits\QueueLogTrait;

class Queuelog extends FreePBX_Helpers implements BMO
{
    use QueueLogTrait;

    /** @var BMO */
    private $FreePBX = null;

    /**
     * Queuelog constructor.
     *
     * @param object $freepbx
     * @throws Exception
     */
    public function __construct($freepbx = null)
    {
        if ($freepbx == null) {
            throw new Exception("Not given a FreePBX Object");
        }
        $this->FreePBX = $freepbx;
        $this->db = $freepbx->Database;
    }

    public function install()
    {
        // TODO: Implement install() method.
    }

    public function uninstall()
    {
        // TODO: Implement uninstall() method.
    }

    /**
     * Processes form submission and pre-page actions.
     *
     * @param string $page Display name
     * @return bool
     * @throws Exception
     */
    public function doConfigPageInit($page)
    {
        $action = $this->getReq('action', '');
        $id = $this->getReq('id', '');
        $page = $this->getReq('page', '');

        switch ($page) {
            case 'answered':
                switch ($action) {
                    case 'add':
                        return '';
                        break;
                }

                break;
        }
    }

    /**
     * Adds buttons to the bottom of pages per set conditions
     *
     * @param array $request $_REQUEST
     *
     * @return array
     */
    public function getActionBar($request)
    {
        switch($request['display']) {
            case 'queuelog':
                $buttons = [
                    'delete' => ['name' => 'delete', 'id' => 'delete', 'value' => _('Excluir'),],
                    'reset' => ['name' => 'reset', 'id' => 'reset', 'value' => _("Redefinir"),],
                    'submit' => ['name' => 'submit', 'id' => 'submit', 'value' => _("Enviar"),],
                ];

                if (!isset($request['id']) || trim($request['id']) == '') {
                    unset($buttons['delete']);
                }
                if (empty($request['view']) || $request['view'] != 'form') {
                    $buttons = [];
                }
                break;
        }
        return $buttons;
    }

    /**
     * Returns bool permissions for AJAX commands
     * https://wiki.freepbx.org/x/XoIzAQ
     * @param string $command The ajax command
     * @param array $setting ajax settings for this command typically untouched
     * @return bool
     */
    public function ajaxRequest($command, &$setting) {
        //The ajax request
        switch ($command) {
            case "getJSON":
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Handle Ajax request
     *
     * @return array | bool
     */
    public function ajaxHandler()
    {
        switch($_REQUEST['command']) {
            case "getJSON":
                $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : '';
                if ('grid' == $_REQUEST['jdata']) {
                    switch ($page) {
                        case 'agent_stats':
                            return $this->getAgentStats($_REQUEST);
                            break;
                        case 'queue_stats':
                            return $this->getQueueStats($_REQUEST);
                            break;
                        case 'sla_stats':
                            return $this->getSlaStats($_REQUEST);
                            break;
                        case 'hangup_stats':
                            return $this->getHangupStats($_REQUEST);
                            break;
                        case 'abandoned_stats':
                            return $this->getAbandonedStats($_REQUEST);
                            break;
                        case 'calls_detail':
                            return $this->getCallsDetail($_REQUEST);
                            break;
                        case 'calls_hour':
                            return $this->getCallsHour($_REQUEST);
                            break;
                    }
                }
                break;

            default:
                return json_encode(['status' => false, 'message' => _("Solicitação Inválida")]);
        }
    }

    /**
     * @param $request $_REQUEST
     * @return string
     */
    public function getRightNav($request)
    {
        return load_view(__DIR__."/views/rnav.php",array());
    }

    /**
     * This returns html to the main page
     *
     * @param $page
     * @return string html
     */
    public function showPage($page)
    {
        switch ($page) {
            case 'stats':
                $content = load_view(__DIR__ . '/views/stats/grid.php');
                return load_view(__DIR__.'/views/default.php', ['content' => $content]);
                break;
            case 'detail':
                $content = load_view(__DIR__ . '/views/detail/grid.php');
                return load_view(__DIR__.'/views/default.php', ['content' => $content]);
                break;
            case 'distribution':
                $content = load_view(__DIR__ . '/views/distribution/grid.php');
                return load_view(__DIR__.'/views/default.php', ['content' => $content]);
                break;
        }
    }
}