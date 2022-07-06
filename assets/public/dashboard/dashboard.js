

jQuery(function ($) {

    const  object      = hamfy_object;
    const  destination_monthly = object.destination_monthly;
    const  response_average_m  = object.response_average_m;
    const  reply_count_monthly = object.reply_count_monthly;
    const  courses_statistics  = object.courses_statistics;



    Highcharts.chart('stack-chart', {
        colors: [ '#CAF270', '#75D684', '#25B493', '#009091'  ] ,
        credits: {
            enabled: false
        },
        chart: {
            type: 'column'
        },
        title: {
            display : false ,
            text: ''
        },
        xAxis: {
            categories:destination_monthly.keys[0]
        },
        yAxis: {
            min: 0,
            title: {
                text: ''
            },
            stackLabels: {
                enabled: true,
                style: {
                    fontWeight: 'bold',
                    color: ( // theme
                        Highcharts.defaultOptions.title.style &&
                        Highcharts.defaultOptions.title.style.color
                    ) || 'gray'
                }
            }
        },
        legend: {
            align: 'right',
            x: -30,
            verticalAlign: 'top',
            y: 25,
            floating: true,
            backgroundColor:
                Highcharts.defaultOptions.legend.backgroundColor || 'white',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
        },
        tooltip: {
            headerFormat: '<b>{point.x}</b><br/>',
            pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: false,
                    style: {
                        fontWeight: 'normal' ,
                        fontSize  : '11px'   ,
                        color     : '#fff'
                    }
                },
                pointWidth: 20
            }
        },
        series: [{
            label: 'License',
            name: 'لایسنس',
            data: destination_monthly.first,
        }, {
            label: 'Support',
            name: 'پشتیبانی',
            data: destination_monthly.second ,
        }, {
            label: 'Other',
            name: 'دیگر',
            data: destination_monthly.third ,
        }, {
            label: 'Sale',
            name: 'فروش',
            data:destination_monthly.fourth ,
        }]
    });



    // ["#3e95cd", "#8e5ea2","#3cba9f","#e8c3b9","#c45850"]
    Highcharts.chart('course-chart', {
        colors: ['#2F4858' ,'#009091','#25B493' ,'#75D684' ,'#CAF270'],
        credits: {
            enabled: false
        },
        chart: {
            plotBackgroundColor: null,
            direction: 'rtl',
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormatter: function () { return  ' : <b>' + this.y + '</b>';  } ,
            useHTML: true,
            style: {
                fontSize: '16px',
                fontFamily: 'tahoma',
                direction: 'rtl',
            },
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<p> {point.name} <b style="color: #009091">  %{point.percentage:.1f}  </b> </p>',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                        textOutline: 'none'
                    }
                }
            }
        },
        series: [{
            colorByPoint: true,
            data:courses_statistics
        }]
    });


    Highcharts.chart('salary-chart', {
        colors: ['#2F4858' ,'#CAF270'],
        credits: {
            enabled: false
        },
        title: {
            text: ''
        },
        subtitle: {
            text: ''
        },
        yAxis: {
            title: {
                enabled: false ,
                text: ' '
            }
        },
        xAxis: {
            categories: response_average_m.keys[0]
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },
        plotOptions: {
        },

        series: [{
            name: 'Support',
            data: response_average_m.support
        }, {
            name: 'Master',
            data: response_average_m.master
        }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

    });



    Highcharts.chart('reply-chart', {
        credits: {
            enabled: false
        },
        colors: ['#2F4858' ,'#CAF270'],
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'area'
        },
        accessibility: {
            description: ' '
        },
        title: {
            text: ''
        },
        xAxis: {
            categories:Object.keys(reply_count_monthly.ticket)
        },
        yAxis: {
            title: {
                text: ''
            },
            labels: {
            }
        },
        tooltip: {
            pointFormat: '{series.name} had stockpiled <b>{point.y:,.0f}</b><br/>warheads in {point.x}'
        },
        plotOptions: {

        },
        series: [{
            name: 'پاسخ',
            data: Object.values( reply_count_monthly.reply )
        }, {
            name: 'تیکت',
            data: Object.values( reply_count_monthly.ticket )
        }],
    });



    $(document).on('click' , '.open-list .open-item' , function (){
       let item_key = '#'+ $(this).data('item_key');
       $(document).find('.open-list .open-item>ul:not('+item_key+')').hide();
       $(this).children('ul').toggle();
    });


    $(document).find("#questioners-reply-count").change(function() {
        if( this.checked ) {
            $(document).find(".questioners-reply-count-all").hide();
            $(document).find(".questioners-reply-count-today").show();
        }else{
            $(document).find(".questioners-reply-count-all").show();
            $(document).find(".questioners-reply-count-today").hide();
        }
    });



})
