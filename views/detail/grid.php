<?php
$request = "&queuename=".$_REQUEST['queuename'];
$request .= "&startDate=";
$request .= empty($_REQUEST['startDate'])?date('Y-m-d'):$_REQUEST['startDate'];
$request .= "&startTime=";
$request .= empty($_POST['startTime']) ? '00:00' : $_POST['startTime'];
$request .= "&endDate=";
$request .= empty($_REQUEST['endDate'])?date('Y-m-d'):$_REQUEST['endDate'];
$request .= "&endTime=";
$request .= empty($_POST['endTime']) ? '23:59' : $_POST['endTime'];
?>
<table id="calls_detail"
       data-url="ajax.php?module=queuelog&command=getJSON&jdata=grid&page=calls_detail<?=$request?>"
       data-cache="false"
       data-state-save="true"
       data-state-save-id-table="calls_detail_grid"
       data-toolbar="#toolbar-all"
       data-maintain-selected="true"
       data-show-columns="true"
       data-show-toggle="true"
       data-toggle="table"
       data-pagination="true"
       data-search="true"
       data-show-export="true"
       data-export-footer="true"
       data-show-refresh="true"
       data-page-list="[10, 25, 50, 100, 200, 400, 800, 1600, ALL]"
       class="table table-sm small">
    <caption><b><?php echo _("Detalhe das chamadas")?></b></caption>
    <thead>
    <tr>
        <th data-field="time" data-sortable="true" data-formatter="dateTimeFormatter"><?php echo _("Data")?></th>
        <th data-field="queuename" data-sortable="true"><?php echo _("Fila")?></th>
        <th data-field="event" data-sortable="true"><?php echo _("Evento")?></th>
        <th data-field="agent" data-sortable="true"><?php echo _("Agente")?></th>
        <th data-field="callerid" data-sortable="true"><?php echo _("Cliente")?></th>
        <th data-field="waittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera")?></th>
        <th data-field="ringtime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Ring")?></th>
        <th data-field="calltime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Duração")?></th>
        <th data-field="position" data-sortable="true"><?php echo _("Posição")?></th>
        <th data-field="origposition" data-sortable="true"><?php echo _("Posição inicial")?></th>
    </tr>
    </thead>
</table>

<script type="text/javascript" charset="utf-8">
    function secFormatter(val, row){
        if (!val) return '';
        var hours   = Math.floor(val / 3600);
        var minutes = Math.floor((val - (hours * 3600)) / 60);
        var seconds = val - (hours * 3600) - (minutes * 60);

        // round seconds
        seconds = Math.round(seconds);

        var result = (hours < 10 ? "0" + hours : hours);
        result += ":" + (minutes < 10 ? "0" + minutes : minutes);
        result += ":" + (seconds  < 10 ? "0" + seconds : seconds);
        return result;
    }
    function dateTimeFormatter(val, row) {
        var dateTime = new Date(val);
        return moment(dateTime).format(datetimeformat);
    }
</script>