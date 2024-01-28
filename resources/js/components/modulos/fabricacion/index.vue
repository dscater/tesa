<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Proceso de producción</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <select
                                class="form-control"
                                v-model="filtro.estado"
                                @change="iniciaFiltrado"
                            >
                                <option value="TODOS">TODOS</option>
                                <option value="EN PRODUCCIÓN">
                                    EN PRODUCCIÓN
                                </option>
                                <option value="TERMINADO">TERMINADO</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group mb-3">
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Código"
                                v-model="filtro.codigo"
                                @change="iniciaFiltrado"
                                @keyup="iniciaFiltrado"
                            />
                            <div class="input-group-append">
                                <button
                                    class="btn btn-outline-primary"
                                    type="button"
                                    @click="iniciaFiltrado"
                                >
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-show="!loading">
                    <div class="col-md-4" v-for="item in listSalidaMaterials">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>{{ item.codigo }}</h4>
                                        <p class="mb-1">
                                            <strong>Producto: </strong>
                                            {{ item.producto.nombre }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Cantidad: </strong>
                                            {{ item.cantidad }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Estado: </strong>
                                            <span
                                                class="text-xs badge"
                                                :class="[
                                                    item.estado == 'TERMINADO'
                                                        ? 'badge-success'
                                                        : 'badge-warning',
                                                ]"
                                            >
                                                {{ item.estado }}
                                            </span>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Fecha Salida: </strong>
                                            {{ item.fecha_salida }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <router-link
                                            :to="{
                                                name: 'salida_materials.show',
                                                params: { id: item.id },
                                            }"
                                            class="btn btn-default btn-flat btn-block"
                                        >
                                            <i class="fa fa-eye"></i>
                                        </router-link>
                                    </div>
                                    <div
                                        class="col-md-6"
                                        v-if="item.estado != 'TERMINADO'"
                                    >
                                        <button
                                            class="btn btn-success btn-flat btn-block"
                                            @click="
                                                terminarSalidaMaterial(
                                                    item.id,
                                                    item.codigo
                                                )
                                            "
                                        >
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" v-show="loading">
                    <div class="col-12">
                        <h4 class="w-100 text-center">Cargando...</h4>
                    </div>
                </div>

                <div class="row" v-show="!loading && totalRows > 0">
                    <div class="col-md-12 pb-3 paginacion_portal">
                        <b-pagination
                            class="rounded-0"
                            align="center"
                            v-model="currentPage"
                            :total-rows="totalRows"
                            :per-page="filtro.perPage"
                            aria-controls="my-table"
                        ></b-pagination>
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
            filtro: {
                codigo: "",
                estado: "TODOS",
                perPage: 12,
            },
            fullscreenLoading: true,
            loadingWindow: Loading.service({
                fullscreen: this.fullscreenLoading,
            }),
            currentPage: 1,
            totalRows: 0,
            listSalidaMaterials: [],
            setTimeOutListaMaterials: null,
            loading: false,
        };
    },
    watch: {
        currentPage(newVal) {
            this.getListaSalidaMaterials(newVal);
        },
    },
    mounted() {
        this.loadingWindow.close();
        this.getListaSalidaMaterials();
    },
    methods: {
        iniciaFiltrado() {
            this.loading = true;
            clearInterval(this.setTimeOutListaMaterials);
            this.setTimeOutListaMaterials = setTimeout(() => {
                this.getListaSalidaMaterials();
            }, 400);
        },
        // Listar SalidaMaterials
        getListaSalidaMaterials(page = 1) {
            this.loading = true;
            let url = "/admin/salida_materials/paginado";
            if (page != 1) {
                url += "?page=" + page;
            }
            axios
                .get(url, {
                    params: this.filtro,
                })
                .then((res) => {
                    this.listSalidaMaterials = res.data.salida_materials.data;
                    this.totalRows = res.data.salida_materials.total;
                    this.filtro.perPage = res.data.per_page;
                    this.loading = false;
                });
        },
        terminarSalidaMaterial(id, descripcion) {
            Swal.fire({
                title: "¿Quierés cambiar el estado del registro con este código?",
                html: `<strong>${descripcion}</strong>`,
                showCancelButton: true,
                confirmButtonColor: "#149FDA",
                confirmButtonText: "Si, cambiar",
                cancelButtonText: "No, cancelar",
                denyButtonText: `No, cancelar`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    axios
                        .post("/admin/salida_materials/terminado/" + id, {
                            _method: "put",
                        })
                        .then((res) => {
                            this.getListaSalidaMaterials();
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
    },
};
</script>

<style></style>
