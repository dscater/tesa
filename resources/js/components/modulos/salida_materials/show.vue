<template>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>Salida de Materiales para Fabricación > Ver</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <p>
                                            <strong>Código: </strong>
                                            {{ oSalidaMaterial.codigo }}
                                        </p>
                                        <p>
                                            <strong>Producto: </strong>
                                            {{
                                                oSalidaMaterial.producto.nombre
                                            }}
                                        </p>
                                        <p>
                                            <strong>Cantidad: </strong>
                                            {{ oSalidaMaterial.cantidad }}
                                        </p>
                                        <p>
                                            <strong>Fecha Salida: </strong>
                                            {{ oSalidaMaterial.fecha_salida }}
                                        </p>
                                        <p class="mb-1">
                                            <strong>Estado: </strong>
                                            <span
                                                class="text-xs badge"
                                                :class="[
                                                    oSalidaMaterial.estado ==
                                                    'TERMINADO'
                                                        ? 'badge-success'
                                                        : 'badge-warning',
                                                ]"
                                            >
                                                {{ oSalidaMaterial.estado }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Material</th>
                                                    <th>Cantidad</th>
                                                    <th>Descripción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template
                                                    v-if="
                                                        oSalidaMaterial
                                                            .salida_detalles
                                                            .length > 0
                                                    "
                                                >
                                                    <tr
                                                        v-for="(
                                                            item, index
                                                        ) in oSalidaMaterial.salida_detalles"
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
                                                <template v-else>
                                                    <tr>
                                                        <td
                                                            colspan="4"
                                                            class="text-center"
                                                        >
                                                            NO SE AGREGÓ NINGUN
                                                            MATERIAL
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-md-3">
                                        <router-link
                                            :to="{
                                                name: 'salida_materials.index',
                                            }"
                                            class="btn btn-default btn-flat btn-block"
                                        >
                                            <i class="fa fa-arrow-left"></i>
                                            Volver</router-link
                                        >
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
    props: ["id"],
    data() {
        return {
            permisos: localStorage.getItem("permisos"),
            fullscreenLoading: true,
            loadingWindow: Loading.service({
                fullscreen: this.fullscreenLoading,
            }),
            oSalidaMaterial: {
                id: 0,
                producto_id: "",
                cantidad: "",
                fecha_salida: "",
                estado: "",
                producto: { nombre: "" },
                salida_detalles: [],
            },
        };
    },
    mounted() {
        this.loadingWindow.close();
        this.getSalidaMaterial();
    },
    methods: {
        getSalidaMaterial() {
            axios.get("/admin/salida_materials/" + this.id).then((response) => {
                this.oSalidaMaterial = response.data.salida_material;
            });
        },
    },
};
</script>

<style></style>
