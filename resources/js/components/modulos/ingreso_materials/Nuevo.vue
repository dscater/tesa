<template>
    <div
        class="modal fade"
        :class="{ show: bModal }"
        id="modal-default"
        aria-modal="true"
        role="dialog"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" v-text="tituloModal"></h4>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                        @click="cierraModal"
                    >
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row">
                            <div
                                class="form-group col-md-12"
                                v-if="accion != 'edit'"
                            >
                                <label
                                    :class="{
                                        'text-danger': errors.material_id,
                                    }"
                                    >Seleccionar Material*</label
                                >

                                <el-select
                                    class="w-100"
                                    :class="{
                                        'is-invalid': errors.material_id,
                                    }"
                                    v-model="ingreso_material.material_id"
                                    filterable
                                    remote
                                    reserve-keyword
                                    placeholder="Buscar material"
                                    :remote-method="buscarMaterial"
                                    :loading="loading_buscador"
                                    @change="muestraInfoMaterial"
                                >
                                    <el-option
                                        v-for="item in aux_lista_materials"
                                        :key="item.id"
                                        :value="item.id"
                                        :label="item.nombre"
                                    >
                                    </el-option>
                                </el-select>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors.material_id"
                                    v-text="errors.material_id[0]"
                                ></span>
                            </div>
                            <div class="form-group col-md-12" v-else>
                                <label
                                    :class="{
                                        'text-danger': errors.material_id,
                                    }"
                                    >Material*</label
                                >
                                <input
                                    type="readonly"
                                    class="form-control"
                                    readonly
                                    v-model="ingreso_material.nombre_material"
                                />
                            </div>
                            <div class="form-group col-md-6">
                                <label
                                    :class="{
                                        'text-danger': errors.proveedor_id,
                                    }"
                                    >Seleccionar Proveedor*</label
                                >
                                <el-select
                                    placeholder="Proveedor"
                                    class="w-100"
                                    :class="{
                                        'is-invalid': errors.proveedor_id,
                                    }"
                                    v-model="ingreso_material.proveedor_id"
                                    filterable
                                >
                                    <el-option
                                        v-for="item in listProveedors"
                                        :key="item.id"
                                        :label="item.razon_social"
                                        :value="item.id"
                                    >
                                    </el-option>
                                </el-select>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors.proveedor_id"
                                    v-text="errors.proveedor_id[0]"
                                ></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label
                                    :class="{
                                        'text-danger': errors.cantidad,
                                    }"
                                    >Cantidad*</label
                                >
                                <el-input
                                    type="number"
                                    min="0.01"
                                    placeholder="Cantidad"
                                    :class="{ 'is-invalid': errors.cantidad }"
                                    v-model="ingreso_material.cantidad"
                                    clearable
                                >
                                </el-input>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors.cantidad"
                                    v-text="errors.cantidad[0]"
                                ></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label
                                    :class="{
                                        'text-danger': errors.peso,
                                    }"
                                    >Peso (kg)*</label
                                >
                                <el-input
                                    type="number"
                                    min="0.01"
                                    placeholder="Peso (kg)"
                                    :class="{ 'is-invalid': errors.peso }"
                                    v-model="ingreso_material.peso"
                                    clearable
                                >
                                </el-input>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors.peso"
                                    v-text="errors.peso[0]"
                                ></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label
                                    :class="{
                                        'text-danger': errors.precio,
                                    }"
                                    >Precio*</label
                                >
                                <el-input
                                    type="number"
                                    min="0.01"
                                    placeholder="Precio"
                                    :class="{ 'is-invalid': errors.precio }"
                                    v-model="ingreso_material.precio"
                                    clearable
                                >
                                </el-input>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors.precio"
                                    v-text="errors.precio[0]"
                                ></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label
                                    :class="{
                                        'text-danger': errors.fecha_ingreso,
                                    }"
                                    >Fecha de ingreso*</label
                                >
                                <el-input
                                    type="date"
                                    placeholder="Fecha de ingreso"
                                    :class="{
                                        'is-invalid': errors.fecha_ingreso,
                                    }"
                                    v-model="ingreso_material.fecha_ingreso"
                                    clearable
                                >
                                </el-input>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors.fecha_ingreso"
                                    v-text="errors.fecha_ingreso[0]"
                                ></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label
                                    :class="{
                                        'text-danger': errors.tipo_ingreso_id,
                                    }"
                                    >Seleccionar Tipo de ingreso*</label
                                >
                                <el-select
                                    placeholder="Tipo de ingreso"
                                    class="w-100"
                                    :class="{
                                        'is-invalid': errors.tipo_ingreso_id,
                                    }"
                                    v-model="ingreso_material.tipo_ingreso_id"
                                    filterable
                                >
                                    <el-option
                                        v-for="item in listTipoIngresos"
                                        :key="item.id"
                                        :label="item.nombre"
                                        :value="item.id"
                                    >
                                    </el-option>
                                </el-select>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors.tipo_ingreso_id"
                                    v-text="errors.tipo_ingreso_id[0]"
                                ></span>
                            </div>
                            <div class="form-group col-md-6">
                                <label
                                    :class="{
                                        'text-danger': errors.descripcion,
                                    }"
                                    >Descripción</label
                                >
                                <el-input
                                    type="textarea"
                                    autosize
                                    placeholder="Descripción"
                                    :class="{
                                        'is-invalid': errors.descripcion,
                                    }"
                                    v-model="ingreso_material.descripcion"
                                    clearable
                                >
                                </el-input>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors.descripcion"
                                    v-text="errors.descripcion[0]"
                                ></span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button
                        type="button"
                        class="btn btn-default"
                        data-dismiss="modal"
                        @click="cierraModal"
                    >
                        Cerrar
                    </button>
                    <el-button
                        type="primary"
                        class="bg-primary"
                        :loading="enviando"
                        @click="setRegistroModal()"
                        >{{ textoBoton }}</el-button
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        muestra_modal: {
            type: Boolean,
            default: false,
        },
        accion: {
            type: String,
            default: "nuevo",
        },
        ingreso_material: {
            type: Object,
            default: {
                id: 0,
                material_id: "",
                proveedor_id: "",
                descripcion: "",
                cantidad: "",
                peso: "",
                precio: "",
                tipo_ingreso_id: "",
                fecha_ingreso: "",
            },
        },
    },
    watch: {
        muestra_modal: function (newVal, oldVal) {
            this.errors = [];
            if (newVal) {
                if (this.accion == "nuevo") {
                    this.ingreso_material.fecha_ingreso = this.getFechaActual();
                }
                this.bModal = true;
            } else {
                this.bModal = false;
            }
        },
    },
    computed: {
        tituloModal() {
            if (this.accion == "nuevo") {
                return "NUEVO REGISTRO";
            } else {
                return "MODIFICAR REGISTRO";
            }
        },
        textoBoton() {
            if (this.accion == "nuevo") {
                return "Registrar";
            } else {
                return "Actualizar";
            }
        },
    },
    data() {
        return {
            user: JSON.parse(localStorage.getItem("user")),
            bModal: this.muestra_modal,
            enviando: false,
            errors: [],
            listMaterials: [],
            aux_lista_materials: [],
            listProveedors: [],
            listTipoIngresos: [],
            loading_buscador: false,
            timeOutMaterials: null,
            sw_busqueda: "todos",
            oMaterial: {
                nombre: "",
                descripcion: "",
            },
        };
    },
    mounted() {
        this.bModal = this.muestra_modal;
        this.getProveedors();
        this.getTipoIngresos();
    },
    methods: {
        getProveedors() {
            axios.get("/admin/proveedors").then((response) => {
                this.listProveedors = response.data.proveedors;
            });
        },
        getTipoIngresos() {
            axios.get("/admin/tipo_ingresos").then((response) => {
                this.listTipoIngresos = response.data.tipo_ingresos;
            });
        },
        setRegistroModal() {
            this.enviando = true;
            try {
                this.textoBtn = "Enviando...";
                let url = "/admin/ingreso_materials";
                let config = {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                };
                let formdata = new FormData();
                formdata.append(
                    "material_id",
                    this.ingreso_material.material_id
                        ? this.ingreso_material.material_id
                        : ""
                );
                formdata.append(
                    "proveedor_id",
                    this.ingreso_material.proveedor_id
                        ? this.ingreso_material.proveedor_id
                        : ""
                );
                formdata.append(
                    "descripcion",
                    this.ingreso_material.descripcion
                        ? this.ingreso_material.descripcion
                        : ""
                );
                formdata.append(
                    "cantidad",
                    this.ingreso_material.cantidad
                        ? this.ingreso_material.cantidad
                        : ""
                );
                formdata.append(
                    "peso",
                    this.ingreso_material.peso ? this.ingreso_material.peso : ""
                );
                formdata.append(
                    "precio",
                    this.ingreso_material.precio
                        ? this.ingreso_material.precio
                        : ""
                );
                formdata.append(
                    "tipo_ingreso_id",
                    this.ingreso_material.tipo_ingreso_id
                        ? this.ingreso_material.tipo_ingreso_id
                        : ""
                );
                formdata.append(
                    "fecha_ingreso",
                    this.ingreso_material.fecha_ingreso
                        ? this.ingreso_material.fecha_ingreso
                        : ""
                );
                if (this.accion == "edit") {
                    url =
                        "/admin/ingreso_materials/" + this.ingreso_material.id;
                    formdata.append("_method", "PUT");
                }
                axios
                    .post(url, formdata, config)
                    .then((res) => {
                        this.enviando = false;
                        if (res.data.sw) {
                            Swal.fire({
                                icon: "success",
                                title: res.data.msj,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                            this.limpiaIngresoMaterial();
                            this.$emit("envioModal");
                            this.errors = [];
                            if (this.accion == "edit") {
                                this.textoBtn = "Actualizar";
                            } else {
                                this.textoBtn = "Registrar";
                            }
                        } else {
                            Swal.fire({
                                icon: "info",
                                title: "Atención",
                                html: res.data.msj,
                                showConfirmButton: false,
                                timer: 2000,
                            });
                        }
                    })
                    .catch((error) => {
                        this.enviando = false;
                        if (this.accion == "edit") {
                            this.textoBtn = "Actualizar";
                        } else {
                            this.textoBtn = "Registrar";
                        }
                        if (error.response) {
                            if (error.response.status === 422) {
                                this.errors = error.response.data.errors;
                            }
                            if (
                                error.response.status === 420 ||
                                error.response.status === 419 ||
                                error.response.status === 401
                            ) {
                                window.location = "/";
                            }
                            if (error.response.status === 500) {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    html: error.response.data.message,
                                    showConfirmButton: false,
                                    timer: 2000,
                                });
                            }
                        }
                    });
            } catch (e) {
                this.enviando = false;
                console.log(e);
            }
        },
        // Dialog/modal
        cierraModal() {
            this.bModal = false;
            this.$emit("close");
        },
        limpiaIngresoMaterial() {
            this.errors = [];
            // this.oIngresoMaterial.material_id = "";
            // this.oIngresoMaterial.proveedor_id = "";
            // this.oIngresoMaterial.descripcion = "";
            // this.oIngresoMaterial.cantidad = "";
            // this.oIngresoMaterial.tipo_ingreso_id = "";
            // this.oIngresoMaterial.fecha_ingreso = "";
        },
        buscarMaterial(query) {
            this.aux_lista_materials = [];
            this.loading_buscador = true;
            clearTimeout(this.timeOutMaterials);
            let self = this;
            this.timeOutMaterials = setTimeout(() => {
                self.getMaterialsQuery(query);
            }, 1000);
        },
        getMaterialsQuery(query) {
            if (query !== "") {
                axios
                    .get("/admin/materials/buscar_material", {
                        params: {
                            value: query,
                            sw_busqueda: this.sw_busqueda,
                        },
                    })
                    .then((response) => {
                        this.loading_buscador = false;
                        this.listMaterials;
                        this.aux_lista_materials = response.data;
                    });
            } else {
                this.loading_buscador = false;
                this.aux_lista_materials = [];
            }
        },
        muestraInfoMaterial() {
            if (this.ingreso_material.material_id != "") {
                let elem = this.aux_lista_materials.filter(
                    (element) => element.id == this.ingreso_material.material_id
                );
                this.oMaterial = elem[0];
            } else {
                this.oMaterial = null;
            }
        },
        getFechaActual() {
            return this.$moment().format("YYYY-MM-DD");
        },
    },
};
</script>

<style></style>
