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
<div class="row">
    <div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo _("Distribuição por hora")?>
        </div>
        <div class="panel-body">
            <div>
                <canvas id="myChart" height="250"></canvas>
            </div>
        </div>
    </div>
    </div>
</div>



<script>
    $(document).ready(function(){
        $.ajax({
            url: "ajax.php?module=queuelog&command=getJSON&jdata=grid&page=calls_hour<?=$request?>",
            method: "GET",
            success: function(data) {
                var hour = [];
                var answered = [];
                var unanswered = [];

                for(var i in data) {
                    hour.push(data[i].hour + ':00');
                    answered.push(data[i].answered);
                    unanswered.push(data[i].unanswered);
                }

                var chartdata = {
                    labels: hour,
                    datasets : [
                        {
                            label: 'Atendidas',
                            fill: false,
                            lineTension: 0.3,
                            borderColor: '#00c0ef',
                            backgroundColor: '#00c0ef',
                            data: answered
                        },
                        {
                            label: 'Perdidas',
                            fill: false,
                            lineTension: 0.3,
                            borderColor: '#f39c12',
                            backgroundColor: '#f39c12',
                            data: unanswered
                        }
                    ]
                };

                var ctx = $("#myChart");

                var lineGraph = new Chart(ctx, {
                    type: 'line',
                    data: chartdata,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            },
            error: function(data) {
                console.log(data);
            }
        });
    });
</script>