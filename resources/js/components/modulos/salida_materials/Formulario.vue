tr
<template>
    <div class="row">
        <div class="form-group col-md-4">
            <label
                :class="{
                    'text-danger': errors.producto_id,
                }"
                >Seleccionar Producto*</label
            >
            <el-select
                placeholder="Producto"
                class="w-100"
                :class="{
                    'is-invalid': errors.producto_id,
                }"
                v-model="salida_material.producto_id"
                filterable
            >
                <el-option
                    v-for="item in listProductos"
                    :key="item.id"
                    :label="
                        item.codigo_almacen +
                        ' | ' +
                        item.codigo_producto +
                        ' | ' +
                        item.nombre
                    "
                    :value="item.id"
                >
                </el-option>
            </el-select>
            <span
                class="error invalid-feedback"
                v-if="errors.producto_id"
                v-text="errors.producto_id[0]"
            ></span>
        </div>
        <div class="form-group col-md-4">
            <label
                :class="{
                    'text-danger': errors.cantidad,
                }"
                >Cantidad de Productos*</label
            >
            <input
                type="number"
                min="0.01"
                placeholder="Cantidad de Productos"
                class="form-control"
                :class="{ 'is-invalid': errors.cantidad }"
                v-model="salida_material.cantidad"
            />
            <span
                class="error invalid-feedback"
                v-if="errors.cantidad"
                v-text="errors.cantidad[0]"
            ></span>
        </div>
        <div class="form-group col-md-4">
            <label
                :class="{
                    'text-danger': errors.fecha_salida,
                }"
                >Fecha de Salida*</label
            >
            <input
                type="date"
                placeholder="Fecha de Salida"
                class="form-control"
                :class="{ 'is-invalid': errors.fecha_salida }"
                v-model="salida_material.fecha_salida"
            />
            <span
                class="error invalid-feedback"
                v-if="errors.fecha_salida"
                v-text="errors.fecha_salida[0]"
            ></span>
        </div>
        <div class="col-12">
            <hr />
            <h4 class="w-100 text-center">MATERIALES</h4>
        </div>
        <div class="col-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Cantidad</th>
                        <th>Descripción</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="salida_material.salida_detalles.length > 0">
                        <tr
                            v-for="(
                                item, index
                            ) in salida_material.salida_detalles"
                        >
                            <td>
                                <el-select
                                    v-if="salida_material.estado != 'TERMINADO'"
                                    type="text"
                                    placeholder="- Seleccionar -"
                                    class="w-100"
                                    :class="{
                                        'is-invalid':
                                            errors['material_id_' + index],
                                    }"
                                    v-model="item.material_id"
                                    filterable
                                >
                                    <el-option
                                        v-for="item_mat in listMaterials"
                                        :key="item_mat.id"
                                        :value="item_mat.id"
                                        :label="item_mat.nombre"
                                    ></el-option>
                                </el-select>
                                <span v-else>
                                    {{ item.material.nombre }}
                                </span>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors['material_id_' + index]"
                                    v-text="errors['material_id_' + index][0]"
                                ></span>
                            </td>
                            <td>
                                <input
                                    v-if="salida_material.estado != 'TERMINADO'"
                                    type="number"
                                    step="0.01"
                                    placeholder="Cantidad"
                                    class="form-control"
                                    :class="{
                                        'is-invalid':
                                            errors['cantidad_' + index],
                                    }"
                                    v-model="item.cantidad"
                                />
                                <span v-else>
                                    {{ item.cantidad }}
                                </span>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors['cantidad_' + index]"
                                    v-text="errors['cantidad_' + index][0]"
                                ></span>
                            </td>
                            <td>
                                <input
                                    v-if="salida_material.estado != 'TERMINADO'"
                                    type="text"
                                    placeholder="Descripción"
                                    class="form-control"
                                    :class="{
                                        'is-invalid':
                                            errors['descripcion_' + index],
                                    }"
                                    v-model="item.descripcion"
                                />
                                <span v-else>
                                    {{ item.descripcion }}
                                </span>
                                <span
                                    class="error invalid-feedback"
                                    v-if="errors['descripcion_' + index]"
                                    v-text="errors['descripcion_' + index][0]"
                                ></span>
                            </td>
                            <td>
                                <button
                                    v-if="salida_material.estado != 'TERMINADO'"
                                    class="btn btn-danger"
                                    @click="eliminarMaterial(index, item.id)"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="4" class="text-center">
                                NO SE AGREGÓ NINGUN MATERIAL
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot v-if="salida_material.estado != 'TERMINADO'">
                    <tr>
                        <td colspan="4" class="p-0">
                            <button
                                class="btn btn-primary bg-navy btn-flat btn-block"
                                @click="agregarMaterial"
                            >
                                <i class="fa fa-plus"></i> Agregar Material
                            </button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col-md-4" v-if="salida_material.estado != 'TERMINADO'">
            <button
                class="btn btn-primary btn-flat btn-block"
                :disabled="enviando"
                v-html="textoBoton"
                @click="enviarFormulario()"
            ></button>
        </div>
        <div class="col-md-2">
            <router-link
                :to="{ name: 'salida_materials.index' }"
                class="btn btn-default btn-flat btn-block"
            >
                <i class="fa fa-arrow-left"></i> Volver</router-link
            >
        </div>
    </div>
</template>

<script>
export default {
    props: {
        accion: {
            type: String,
            default: "nuevo",
        },
        salida_material: {
            type: Object,
            default: {
                id: 0,
                producto_id: "",
                cantidad: "",
                fecha_salida: "",
                estado: "",
                salida_detalles: [],
            },
        },
    },
    watch: {
        salida_material(newVal) {
            if (newVal == "nuevo") {
                this.salida_material.fecha_salida = this.getFechaActual();
            }
        },
    },
    computed: {
        textoBoton() {
            if (this.enviando) {
                return `<i class="fa fa-spinner fa-spin"></i> Enviando...`;
            }

            if (this.accion == "nuevo") {
                return `<i class="fa fa-save"></i> Registrar`;
            } else {
                return `<i class="fa fa-edit"></i> Actualizar`;
            }
        },
    },
    data() {
        return {
            user: JSON.parse(localStorage.getItem("user")),
            enviando: false,
            errors: [],
            listMaterials: [],
            listProductos: [],
            eliminados: [],
        };
    },
    mounted() {
        if (this.accion == "nuevo") {
            this.salida_material.fecha_salida = this.getFechaActual();
        }
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
        enviarFormulario() {
            this.enviando = true;
            try {
                let url = "/admin/salida_materials";
                if (this.accion == "edit") {
                    url = "/admin/salida_materials/" + this.salida_material.id;
                    this.salida_material["_method"] = "put";
                    this.salida_material["eliminados"] = this.eliminados;
                }
                axios
                    .post(url, this.salida_material)
                    .then((res) => {
                        this.enviando = false;
                        if (res.data.sw) {
                            Swal.fire({
                                icon: "success",
                                title: res.data.msj,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                            this.errors = [];
                            this.$router.push({
                                name: "salida_materials.index",
                            });
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
                                    showConfirmButton: true,
                                    confirmButtonColor: "#149FDA",
                                    confirmButtonText: "Aceptar",
                                });
                            }
                        }
                    });
            } catch (e) {
                this.enviando = false;
                console.log(e);
            }
        },
        agregarMaterial() {
            this.salida_material.salida_detalles.push({
                salida_material_id: "",
                material_id: "",
                cantidad: "",
                descripcion: "",
            });
        },
        eliminarMaterial(index, id) {
            if (id != 0) {
                this.eliminados.push(id);
            }
            this.salida_material.salida_detalles.splice(index, 1);
        },

        getFechaActual() {
            return this.$moment().format("YYYY-MM-DD");
        },
    },
};
</script>

<style></style>
