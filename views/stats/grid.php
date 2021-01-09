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
<table id="queue_stats"
       data-url="ajax.php?module=queuelog&command=getJSON&jdata=grid&page=queue_stats<?=$request?>"
       data-cache="false"
       data-state-save="true"
       data-state-save-id-table="queue_stats_grid"
       data-toolbar="#toolbar-all"
       data-toggle="table"
       class="table table-sm small">
    <caption><b><?php echo _("Chamadas atendidas por fila")?></b></caption>
    <thead>
    <tr>
        <th data-field="queuename" data-sortable="true"><?php echo _("Fila")?></th>
        <th data-field="calls" data-sortable="true"><?php echo _("Chamadas")?></th>
        <th data-field="waittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera total")?></th>
        <th data-field="avgwaittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera média")?></th>
        <th data-field="maxwaittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera máxima")?></th>
        <th data-field="calltime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Duração total")?></th>
        <th data-field="avgcalltime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Duração média")?></th>
        <th data-field="maxcalltime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Duração máxima")?></th>
    </tr>
    </thead>
</table>
<table id="agent_stats"
       data-url="ajax.php?module=queuelog&command=getJSON&jdata=grid&page=agent_stats<?=$request?>"
       data-cache="false"
       data-state-save="true"
       data-state-save-id-table="agent_stats_grid"
       data-toolbar="#toolbar-all"
       data-toggle="table"
       class="table table-sm small">
    <caption><b><?php echo _("Chamadas atendidas por agente")?></b></caption>
    <thead>
		<tr>
            <th data-field="agent" data-sortable="true"><?php echo _("Agente")?></th>
            <th data-field="queuename" data-sortable="true"><?php echo _("Fila")?></th>
            <th data-field="calls" data-sortable="true"><?php echo _("Chamadas")?></th>
            <th data-field="waittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera total")?></th>
            <th data-field="avgwaittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera média")?></th>
            <th data-field="maxwaittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera máxima")?></th>
            <th data-field="calltime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Duração total")?></th>
            <th data-field="avgcalltime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Duração média")?></th>
            <th data-field="maxcalltime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Duração máxima")?></th>
		</tr>
	</thead>
</table>
<table id="abandoned_stats"
       data-url="ajax.php?module=queuelog&command=getJSON&jdata=grid&page=abandoned_stats<?=$request?>"
       data-cache="false"
       data-state-save="true"
       data-state-save-id-table="abandoned_stats_grid"
       data-toolbar="#toolbar-all"
       data-maintain-selected="true"
       data-toggle="table"
       class="table table-sm small">
    <caption><b><?php echo _("Chamadas perdidas")?></b></caption>
    <thead>
    <tr>
        <th data-field="event" data-sortable="true"><?php echo _("Causa")?></th>
        <th data-field="calls" data-sortable="true"><?php echo _("Chamadas")?></th>
        <th data-field="origposition" data-sortable="true"><?php echo _("Posição média inicial")?></th>
        <th data-field="position" data-sortable="true"><?php echo _("Posição média")?></th>
        <th data-field="waittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera média")?></th>
        <th data-field="maxwaittime" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera máxima")?></th>
    </tr>
    </thead>
</table>
<div class="row">
    <div class="col-sm-6">
        <table id="sla_stats"
               data-url="ajax.php?module=queuelog&command=getJSON&jdata=grid&page=sla_stats<?=$request?>"
               data-cache="false"
               data-state-save="true"
               data-state-save-id-table="sla_stats_grid"
               data-toolbar="#toolbar-all"
               data-maintain-selected="true"
               data-toggle="table"
               class="table table-sm small">
            <caption><b><?php echo _("Nível de Serviço")?></b></caption>
            <thead>
            <tr>
                <th data-field="sla" data-sortable="true" data-formatter="secFormatter"><?php echo _("Espera de até")?></th>
                <th data-field="calls" data-sortable="true"><?php echo _("Chamadas")?></th>
                <th data-field="delta" data-sortable="true"><?php echo _("Delta")?></th>
                <th data-field="percentage" data-sortable="true"><?php echo _("Porcentagem")?></th>

            </tr>
            </thead>
        </table>
    </div>
    <div class="col-sm-6">

        <table id="hangup_stats"
               data-url="ajax.php?module=queuelog&command=getJSON&jdata=grid&page=hangup_stats<?=$request?>"
               data-cache="false"
               data-state-save="true"
               data-state-save-id-table="hangup_stats_grid"
               data-toolbar="#toolbar-all"
               data-maintain-selected="true"
               data-toggle="table"
               class="table table-sm small">
            <caption><b><?php echo _("Causa da desconexão")?></b></caption>
            <thead>
            <tr>
                <th data-field="event" data-sortable="true"><?php echo _("Causa")?></th>
                <th data-field="calls" data-sortable="true"><?php echo _("Quantidade")?></th>
                <th data-field="percentage" data-sortable="true"><?php echo _("Porcentagem")?></th>
            </tr>
            </thead>
        </table>

    </div>
</div>
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
</script>