<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Cuantificador de Producción</h1>
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
                                <div class="row">
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Seleccionar producto:
                                            </div>
                                            <div class="col-9">
                                                <el-select
                                                    class="w-100"
                                                    v-model="
                                                        oCuantificador.producto_id
                                                    "
                                                    filterable
                                                    clearable
                                                >
                                                    <el-option
                                                        v-for="item in listProductos"
                                                        :key="item.id"
                                                        :value="item.id"
                                                        :label="item.nombre"
                                                    ></el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Seleccionar materiales:
                                            </div>
                                            <div class="col-9">
                                                <el-select
                                                    class="w-100"
                                                    v-model="
                                                        oCuantificador.array_materials
                                                    "
                                                    filterable
                                                    multiple
                                                >
                                                    <el-option
                                                        v-for="item in listMaterials"
                                                        :key="item.id"
                                                        :value="item.id"
                                                        :label="item.nombre"
                                                    ></el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Cantidad Prendas Docenas:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.c_prendas_docenas
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Tiempo Horas:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.tiempo_horas
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Seleccionar maquina tejedora:
                                            </div>
                                            <div class="col-9">
                                                <el-select
                                                    class="w-100"
                                                    v-model="
                                                        oCuantificador.maquina_tejedora
                                                    "
                                                    filterable
                                                    clearable
                                                >
                                                    <el-option
                                                        v-for="(
                                                            item, index
                                                        ) in listMaquinas"
                                                        :key="index"
                                                        :value="index"
                                                        :label="item"
                                                    ></el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Cantidad de maquinas:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.c_maquinas
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Promedio timepo de fabricación 1
                                                Prenda:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.p_tfabricacion
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 offset-md-4">
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-block btn-flat"
                                            @click="calcular"
                                            :disabled="calculando"
                                            v-html="txtButton"
                                        ></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <h4 class="text-center">RESULTADO</h4>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Promedio tiempo fabricación 1
                                                Prenda:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.r_ptfabricacion
                                                    "
                                                    readonly
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Resultado de tiempo requerido 1
                                                Prenda:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.r_trprenda
                                                    "
                                                    readonly
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Docenas fabricados:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.r_docenas_fabricados
                                                    "
                                                    readonly
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Pares fabricados:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.r_pares_fabricados
                                                    "
                                                    readonly
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 form-group">
                                        <div class="row">
                                            <div
                                                class="col-3 text-right d-flex align-items-center justify-content-end"
                                            >
                                                Unidades fabricados:
                                            </div>
                                            <div class="col-9">
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    v-model="
                                                        oCuantificador.r_ufabricados
                                                    "
                                                    readonly
                                                />
                                            </div>
                                        </div>
                                    </div>
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
            permisos: localStorage.getItem("permisos"),
            fullscreenLoading: true,
            loadingWindow: Loading.service({
                fullscreen: this.fullscreenLoading,
            }),
            listProductos: [],
            listMaterials: [],
            listMaquinas: [
                "MATEC MONO 4",
                "LONATI L404R",
                "BENTLEY",
                "LONATI DL3C",
                "JERSEY",
                "SINGER",
            ],
            oCuantificador: {
                producto_id: "",
                array_materials: [],
                c_prendas_docenas: 1,
                tiempo_horas: 0,
                maquina_tejedora: "",
                c_maquinas: 1,
                p_tfabricacion: 60,
                r_ptfabricacion: 0,
                r_trprenda: 0,
                r_docenas_fabricados: 0,
                r_pares_fabricados: 0,
                r_ufabricados: 0,
            },
            calculando: false,
        };
    },
    computed: {
        txtButton() {
            if (this.calculando) {
                return "CALCULANDO...";
            }
            return "CALCULAR";
        },
    },
    mounted() {
        this.loadingWindow.close();
        this.getProductos();
        this.getMaterials();
    },
    methods: {
        getProductos() {
            axios.get("/admin/productos").then((response) => {
                this.listProductos = response.data.productos;
            });
        },
        getMaterials() {
            axios.get("/admin/materials").then((response) => {
                this.listMaterials = response.data.materials;
            });
        },
        calcular() {
            this.calculando = true;
            this.oCuantificador.r_ptfabricacion = parseFloat(
                this.oCuantificador.p_tfabricacion
            );
            this.oCuantificador.r_trprenda =
                ((parseFloat(this.oCuantificador.tiempo_horas) * 3600) /
                    (parseFloat(this.oCuantificador.c_prendas_docenas) * 24)) *
                parseFloat(this.oCuantificador.c_maquinas);

            this.oCuantificador.r_trprenda = parseFloat(
                this.oCuantificador.r_trprenda
            ).toFixed(0) + ' (SEGUNDOS)';

            this.oCuantificador.r_docenas_fabricados = parseFloat(
                this.oCuantificador.c_prendas_docenas
            );

            this.oCuantificador.r_pares_fabricados =
                parseFloat(this.oCuantificador.c_prendas_docenas) * 12;
            this.oCuantificador.r_pares_fabricados = parseFloat(
                this.oCuantificador.r_pares_fabricados
            ).toFixed(0);

            this.oCuantificador.r_ufabricados =
                parseFloat(this.oCuantificador.c_prendas_docenas) * 24;
            this.oCuantificador.r_ufabricados = parseFloat(
                this.oCuantificador.r_ufabricados
            ).toFixed(0);

            setTimeout(() => {
                this.calculando = false;
            }, 600);
        },
    },
};
</script>

<style></style>
