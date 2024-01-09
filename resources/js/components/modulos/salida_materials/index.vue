<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>Salida de Materiales para Fabricación</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-3">
                                        <router-link
                                            v-if="
                                                permisos.includes(
                                                    'salida_materials.create'
                                                )
                                            "
                                            :to="{
                                                name: 'salida_materials.create',
                                            }"
                                            class="btn btn-primary btn-flat btn-block"
                                        >
                                            <i class="fa fa-plus"></i>
                                            Nuevo
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <b-col lg="10" class="my-1">
                                        <b-form-group
                                            label="Buscar"
                                            label-for="filter-input"
                                            label-cols-sm="3"
                                            label-align-sm="right"
                                            label-size="sm"
                                            class="mb-0"
                                        >
                                            <b-input-group size="sm">
                                                <b-form-input
                                                    id="filter-input"
                                                    v-model="filter"
                                                    type="search"
                                                    placeholder="Buscar"
                                                ></b-form-input>

                                                <b-input-group-append>
                                                    <b-button
                                                        class="bg-primary"
                                                        variant="primary"
                                                        :disabled="!filter"
                                                        @click="filter = ''"
                                                        >Borrar</b-button
                                                    >
                                                </b-input-group-append>
                                            </b-input-group>
                                        </b-form-group>
                                    </b-col>
                                    <div class="col-md-12">
                                        <b-overlay
                                            :show="showOverlay"
                                            rounded="sm"
                                        >
                                            <b-table
                                                :fields="fields"
                                                :items="listRegistros"
                                                show-empty
                                                stacked="md"
                                                responsive
                                                :current-page="currentPage"
                                                :per-page="perPage"
                                                @filtered="onFiltered"
                                                empty-text="Sin resultados"
                                                empty-filtered-text="Sin resultados"
                                                :filter="filter"
                                            >
                                                <template #cell(estado)="row">
                                                    <span
                                                        class="text-sm badge"
                                                        :class="{
                                                            'badge-warning':
                                                                row.item
                                                                    .estado ==
                                                                'EN PRODUCCIÓN',
                                                            'badge-success':
                                                                row.item
                                                                    .estado ==
                                                                'TERMINADO',
                                                        }"
                                                        >{{
                                                            row.item.estado
                                                        }}</span
                                                    >
                                                </template>

                                                <template #cell(mas)="row">
                                                    <b-button
                                                        variant="warning"
                                                        size="sm"
                                                        @click="
                                                            row.toggleDetails
                                                        "
                                                    >
                                                        {{
                                                            row.detailsShowing
                                                                ? "Ocultar"
                                                                : "Mostrar"
                                                        }}
                                                        Detalles
                                                    </b-button>
                                                </template>

                                                <template #row-details="row">
                                                    <b-card>
                                                        <b-row
                                                            class="mb-2"
                                                            style="
                                                                overflow: auto;
                                                            "
                                                        >
                                                            <b-col cols="12">
                                                                <table
                                                                    class="table table-bordered"
                                                                >
                                                                    <thead>
                                                                        <tr>
                                                                            <th>
                                                                                Material
                                                                            </th>
                                                                            <th>
                                                                                Cantidad
                                                                            </th>
                                                                            <th>
                                                                                Descripción
                                                                            </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <template
                                                                            v-if="
                                                                                row
                                                                                    .item
                                                                                    .salida_detalles
                                                                                    .length >
                                                                                0
                                                                            "
                                                                        >
                                                                            <tr
                                                                                v-for="(
                                                                                    item,
                                                                                    index
                                                                                ) in row
                                                                                    .item
                                                                                    .salida_detalles"
                                                                            >
                                                                                <td>
                                                                                    <span>
                                                                                        {{
                                                                                            item
                                                                                                .material
                                                                                                .nombre
                                                                                        }}
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    <span>
                                                                                        {{
                                                                                            item.cantidad
                                                                                        }}
                                                                                    </span>
                                                                                </td>
                                                                                <td>
                                                                                    <span>
                                                                                        {{
                                                                                            item.descripcion
                                                                                        }}
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                        </template>
                                                                        <template
                                                                            v-else
                                                                        >
                                                                            <tr>
                                                                                <td
                                                                                    colspan="4"
                                                                                    class="text-center"
                                                                                >
                                                                                    NO
                                                                                    SE
                                                                                    AGREGÓ
                                                                                    NINGUN
                                                                                    MATERIAL
                                                                                </td>
                                                                            </tr>
                                                                        </template>
                                                                    </tbody>
                                                                </table>
                                                            </b-col>
                                                        </b-row>
                                                        <b-button
                                                            size="sm"
                                                            variant="warning"
                                                            @click="
                                                                row.toggleDetails
                                                            "
                                                            >Ocultar</b-button
                                                        >
                                                    </b-card>
                                                </template>

                                                <template #cell(accion)="row">
                                                    <div
                                                        class="row justify-content-between"
                                                    >
                                                        <router-link
                                                            :to="{
                                                                name: 'salida_materials.show',
                                                                params: {
                                                                    id: row.item
                                                                        .id,
                                                                },
                                                            }"
                                                            class="btn btn-outline-primary btn-block rounded-pill"
                                                            title="Ver registro"
                                                        >
                                                            <i
                                                                class="fa fa-eye"
                                                            ></i>
                                                        </router-link>
                                                        <b-button
                                                            v-if="
                                                                row.item
                                                                    .estado ==
                                                                'EN PRODUCCIÓN'
                                                            "
                                                            size="sm"
                                                            pill
                                                            variant="outline-warning"
                                                            class="btn-flat btn-block"
                                                            title="Editar registro"
                                                            @click="
                                                                editarRegistro(
                                                                    row.item
                                                                )
                                                            "
                                                        >
                                                            <i
                                                                class="fa fa-edit"
                                                            ></i>
                                                        </b-button>
                                                        <b-button
                                                            v-if="
                                                                row.item
                                                                    .estado ==
                                                                'EN PRODUCCIÓN'
                                                            "
                                                            size="sm"
                                                            pill
                                                            variant="outline-danger"
                                                            class="btn-flat btn-block"
                                                            title="Eliminar registro"
                                                            @click="
                                                                eliminaSalidaMaterial(
                                                                    row.item.id,
                                                                    row.item
                                                                        .codigo
                                                                )
                                                            "
                                                        >
                                                            <i
                                                                class="fa fa-trash"
                                                            ></i>
                                                        </b-button>
                                                    </div>
                                                </template>
                                            </b-table>
                                        </b-overlay>
                                        <div class="row">
                                            <b-col
                                                sm="6"
                                                md="2"
                                                class="ml-auto my-1"
                                            >
                                                <b-form-select
                                                    align="right"
                                                    id="per-page-select"
                                                    v-model="perPage"
                                                    :options="pageOptions"
                                                    size="sm"
                                                ></b-form-select>
                                            </b-col>
                                            <b-col
                                                sm="6"
                                                md="2"
                                                class="my-1 mr-auto"
                                                v-if="perPage"
                                            >
                                                <b-pagination
                                                    v-model="currentPage"
                                                    :total-rows="totalRows"
                                                    :per-page="perPage"
                                                    align="left"
                                                ></b-pagination>
                                            </b-col>
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
            search: "",
            listRegistros: [],
            showOverlay: false,
            fields: [
                { key: "codigo", label: "Código Salida", sortable: true },
                {
                    key: "producto.nombre",
                    label: "Producto",
                    sortable: true,
                },
                { key: "cantidad", label: "Cantidad", sortable: true },
                { key: "estado", label: "Estado", sortable: true },
                {
                    key: "fecha_salida",
                    label: "Fecha de Salida",
                    sortable: true,
                },
                {
                    key: "fecha_registro",
                    label: "Fecha de registro",
                    sortable: true,
                },
                { key: "mas", label: "Ver más" },
                { key: "accion", label: "Acción" },
            ],
            loading: true,
            fullscreenLoading: true,
            loadingWindow: Loading.service({
                fullscreen: this.fullscreenLoading,
            }),
            muestra_modal: false,
            modal_accion: "nuevo",
            oSalidaMaterial: {
                id: 0,
                material_id: "",
                proveedor_id: "",
                descripcion: "",
                cantidad: "",
                tipo_ingreso_id: "",
                fecha_ingreso: "",
            },
            currentPage: 1,
            perPage: 5,
            pageOptions: [
                { value: 5, text: "Mostrar 5 Registros" },
                { value: 10, text: "Mostrar 10 Registros" },
                { value: 25, text: "Mostrar 25 Registros" },
                { value: 50, text: "Mostrar 50 Registros" },
                { value: 100, text: "Mostrar 100 Registros" },
                { value: this.totalRows, text: "Mostrar Todo" },
            ],
            totalRows: 10,
            filter: null,
        };
    },
    mounted() {
        this.loadingWindow.close();
        this.getSalidaMaterials();
    },
    methods: {
        // Seleccionar Opciones de Tabla
        editarRegistro(item) {
            this.$router.push({
                name: "salida_materials.edit",
                params: {
                    id: item.id,
                },
            });
        },

        // Listar SalidaMaterials
        getSalidaMaterials() {
            this.showOverlay = true;
            this.muestra_modal = false;
            let url = "/admin/salida_materials";
            if (this.pagina != 0) {
                url += "?page=" + this.pagina;
            }
            axios
                .get(url, {
                    params: { per_page: this.per_page },
                })
                .then((res) => {
                    this.showOverlay = false;
                    this.listRegistros = res.data.salida_materials;
                    this.totalRows = res.data.total;
                });
        },
        eliminaSalidaMaterial(id, descripcion) {
            Swal.fire({
                title: "¿Quierés eliminar este registro?",
                html: `<strong>${descripcion}</strong>`,
                showCancelButton: true,
                confirmButtonColor: "#149FDA",
                confirmButtonText: "Si, eliminar",
                cancelButtonText: "No, cancelar",
                denyButtonText: `No, cancelar`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios
                        .post("/admin/salida_materials/" + id, {
                            _method: "DELETE",
                        })
                        .then((res) => {
                            this.getSalidaMaterials();
                            this.filter = "";
                            Swal.fire({
                                icon: "success",
                                title: res.data.msj,
                                showConfirmButton: false,
                                timer: 1500,
                            });
                        })
                        .catch((error) => {
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
                }
            });
        },
        abreModal(tipo_accion = "nuevo", salida_material = null) {
            this.muestra_modal = true;
            this.modal_accion = tipo_accion;
            if (salida_material) {
                this.oSalidaMaterial = salida_material;
            }
        },
        onFiltered(filteredItems) {
            // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length;
            this.currentPage = 1;
        },
        limpiaSalidaMaterial() {
            this.oSalidaMaterial.material_id = "";
            this.oSalidaMaterial.proveedor_id = "";
            this.oSalidaMaterial.descripcion = "";
            this.oSalidaMaterial.cantidad = "";
            this.oSalidaMaterial.tipo_ingreso_id = "";
            this.oSalidaMaterial.fecha_ingreso = "";
        },
        formatoFecha(date) {
            return this.$moment(String(date)).format("DD/MM/YYYY");
        },
    },
};
</script>

<style></style>
