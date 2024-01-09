<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Reportes Estadísticos - % Fabricación</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h4>% de Fabricación - Rango de fechas</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Fecha inicio</label>
                                                <input
                                                    type="date"
                                                    v-model="
                                                        oGrafico1.fecha_ini
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-3">
                                                <label>Fecha fin</label>
                                                <input
                                                    type="date"
                                                    v-model="
                                                        oGrafico1.fecha_fin
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                            <button
                                                class="btn btn-primary"
                                                @click="grafico1()"
                                            >
                                                <i class="fa fa-sync"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-12"
                                        id="container1"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    data() {
        return {
            loadingWindow: Loading.service({
                fullscreen: this.fullscreenLoading,
            }),
            errors: [],
            aFechas: [],
            enviando: false,
            errors: [],
            oGrafico1: {
                anio: "",
                mes: "",
                filtro: "TODOS",
                proveedor: "TODOS",
            },
            listProveedors: [],
            listMeses: [
                {
                    key: "01",
                    label: "Enero",
                },
                {
                    key: "02",
                    label: "Febrero",
                },
                {
                    key: "03",
                    label: "Marzo",
                },
                {
                    key: "04",
                    label: "Abril",
                },
                {
                    key: "05",
                    label: "Mayo",
                },
                {
                    key: "06",
                    label: "Junio",
                },
                {
                    key: "07",
                    label: "Julio",
                },
                {
                    key: "08",
                    label: "Agosto",
                },
                {
                    key: "09",
                    label: "Septiembre",
                },
                {
                    key: "10",
                    label: "Octubre",
                },
                {
                    key: "11",
                    label: "Noviembre",
                },
                {
                    key: "12",
                    label: "Diciembre",
                },
            ],
        };
    },
    watch: {
        oGrafico1: {
            handler: function (newVal, oldVal) {
                this.grafico1();
            },
            deep: true,
        },
    },
    mounted() {
        this.loadingWindow.close();
        this.asignaFechas();
        this.grafico1();
    },
    methods: {
        grafico1() {
            this.enviando = true;
            axios
                .get("/admin/analisis_bi/p_fabricacion1", {
                    params: this.oGrafico1,
                })
                .then((response) => {
                    this.errors = [];
                    let fecha_entero = new Date(
                        this.oGrafico1.fecha_ini
                    ).getTime();

                    Highcharts.chart("container1", {
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                            type: "pie",
                        },
                        title: {
                            text: "% Fabricación",
                        },
                        subtitle: {
                            text: "",
                        },
                        xAxis: {
                            type: "category",
                            crosshair: true,
                            labels: {
                                rotation: -45,
                                style: {
                                    fontSize: "10px",
                                    fontFamily: "Verdana, sans-serif",
                                },
                            },
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: "TOTAL",
                            },
                        },
                        legend: {
                            enabled: true,
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: "pointer",
                                dataLabels: {
                                    enabled: true,
                                    distance: '-30%',
                                    format: "<b>{point.name}</b>: {point.percentage:.1f} %",
                                },
                                showInLegend: true,
                            },
                        },
                        tooltip: {
                            headerFormat:
                                '<span style="font-size:10px"><b>{point.key}</b></span><table>',
                            pointFormat:
                                '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:.2f}</b></td></tr>',
                            footerFormat: "</table>",
                            shared: true,
                            useHTML: true,
                        },

                        series: [
                            {
                                name: "Porcentaje",
                                colorByPoint: true,
                                data: response.data.datos,
                                dataLabels: {
                                    rotation: 0,
                                    color: "#000000",
                                    format: "{point.y:.2f} %", // one decimal
                                    y: 0, // 10 pixels down from the top
                                    style: {
                                        fontSize: "10px",
                                        fontFamily: "Verdana, sans-serif",
                                    },
                                },
                            },
                        ],
                    });
                    this.enviando = false;
                })
                .catch(async (error) => {
                    this.enviando = false;
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.errors = error.response.data.errors;
                        }
                    }
                });
        },
        asignaFechas() {
            let fecha = new Date();
            let fecha_completa = "";
            let anio = fecha.getFullYear();
            let mes = fecha.getMonth();
            let dia = fecha.getDate();
            mes = parseInt(mes + 1);
            fecha_completa = anio + "-" + mes + "-" + dia;
            this.oGrafico1.mes = mes;
            if (parseInt(mes) < 10) {
                fecha_completa = anio + "-0" + mes + "-" + dia;
                this.oGrafico1.mes = "0" + mes;
            }
            this.oGrafico1.fecha_ini = fecha_completa;
            this.oGrafico1.fecha_fin = fecha_completa;
            this.oGrafico1.anio = parseInt(anio);
        },
    },
};
</script>

<style></style>
