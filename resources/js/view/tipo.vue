<template>
    <v-container fluid>
        <v-card outlined>
            <v-card-title>Lista de tipos materia</v-card-title>
            <v-card-text>
                <v-btn @click="open_nuevo=true" outlined color="info">Nuevo tipo</v-btn>
                <v-data-table
                    :headers="header"
                    :items="table.data"
                    :page.sync="table.current_page"
                    hide-default-footer
                    >
                    <template v-slot:item.editar="{ item }">
                        <v-btn text color="warning" @click="buscar(item.id)">
                            <i class="far fa-edit"></i>
                        </v-btn>
                    </template>
                </v-data-table>
                <v-pagination v-model="table.current_page" :length="table.last_page" circle @input="listar"></v-pagination>
            </v-card-text>

            <!-- Nuevo -->
            <v-dialog v-model="open_nuevo" persistent max-width="350">
                <v-card>
                    <v-card-title class="headline">Nuevo tipo</v-card-title>
                    <v-card-text>
                        <v-text-field 
                            label="cod_cartilla" 
                            v-model="tipo.cod_cartilla"
                            :error-messages="error.cod_cartilla"
                        ></v-text-field>
                        <v-text-field 
                            label="Nombre" 
                            v-model="tipo.nombre_tipo"
                            :error-messages="error.nombre_tipo"
                        ></v-text-field>
                        <div>
                            <v-select
                                label="Materia:"
                                v-model="tipo.materia_id"
                                :error-messages="error.materia_id"
                                :items="materias"
                                item-text="nombre_materia"
                                item-value="id">
                                </v-select>
                        </div>
                        <div class="text-right mt-3">
                            <v-btn 
                                outlined 
                                color="secondary" 
                                @click="open_nuevo=false"
                                >Cancelar</v-btn>
                            <v-btn 
                                outlined 
                                color="primary" 
                                @click="guardar()"
                                >Guardar</v-btn>
                        </div>
                    </v-card-text>
                </v-card>               
            </v-dialog>
            <!-- Editar -->
            <v-dialog v-model="open_editar" persistent max-width="350">
                <v-card>
                    <v-card-title class="headline">Editar tipo</v-card-title>
                    <v-card-text>
                        <v-text-field 
                            label="cod_cartilla" 
                            v-model="tipo_editar.cod_cartilla"
                            :error-messages="error_editar.cod_cartilla"
                        ></v-text-field>
                        <v-text-field 
                            required 
                            label="Nombre" 
                            v-model="tipo_editar.nombre_tipo"
                            :error-messages="error_editar.nombre_tipo"
                        ></v-text-field>
                        <v-select
                                label="Materia:"
                                v-model="tipo_editar.materia_id"
                                :error-messages="error_editar.materia_id"
                                :items="materias"
                                item-text="nombre_materia"
                                item-value="id">
                                </v-select>
                        <div class="text-right mt-3">
                            <v-btn 
                                outlined 
                                color="secondary" 
                                @click="open_editar=false"
                                >Cancelar</v-btn>
                            <v-btn 
                                outlined 
                                color="primary" 
                                @click="actualizar()"
                                >Guardar</v-btn>
                        </div>
                    </v-card-text>
                </v-card>               
            </v-dialog>
        </v-card>
    </v-container>
</template>
<script>
export default {
    data() {
        return {
            materias: [],
            header:[
                { text: 'Código Cartilla', value: 'cod_cartilla' },
                { text: 'Descripción', value: 'nombre_tipo' },
                { text: 'Materia', value: 'nombre_materia' },
                { text: 'Editar', value: 'editar' },
            ],
            table: {
                current_page: 1,
                last_page: 1,
                data: []
            },
            search: '',
            //Modal Nuevo
            open_nuevo: false,
            tipo: this.initForm(),
            error: {},
            //Modal Editar
            open_editar: false,
            tipo_editar: this.initForm(),
            error_editar: {},
        }
    },
    mounted() {
        axios.get(url_base+'/materia?all')
        .then(response => {
            this.materias = response.data;
            this.materias.push({
                id: '',
                nombre_materia: 'Seleccione Materia'
            })
        })
        this.listar(1);
    },
    methods: {
        initForm(){
            return {
                cod_cartilla: '',
                nombre_tipo: '',
                materia_id: '',
            }
        },
        listar(n=this.table.current_page){
            axios.get(url_base+'/tipo?page='+n+'&search='+this.search)
            .then(response => {
                this.table = response.data;
            })
        },
        guardar(){
            axios.post(url_base+'/tipo',this.tipo)
            .then(response => {
                var respuesta=response.data;
                switch (respuesta.status) {
                    case 'OK':
                        swal("tipo Creado", { 
                            icon: "success", 
                            timer: 2000, 
                            buttons: false
                        });
                        this.tipo=this.initForm();
                        this.open_nuevo=false;
                        this.listar();
                        break;
                    case 'VALIDATION':
                        this.error=respuesta.data;
                        break;
                    case 'ERROR':
                        break;
                    default:

                        break;
                }
            });
        },
        buscar(id){
            axios.get(url_base+'/tipo/'+id)
            .then(response => {
                this.open_editar=true;
                this.tipo_editar = response.data;
            })
        },
        actualizar(){
            axios.post(url_base+`/tipo/${this.tipo_editar.id}?_method=PUT`,this.tipo_editar)
            .then(response => {
                var respuesta=response.data;
                switch (respuesta.status) {
                    case 'OK':
                        swal("tipo Actualizado", { 
                            icon: "success", 
                            timer: 2000, 
                            buttons: false
                        });
                        this.tipo_editar=this.initForm();
                        this.open_editar=false;
                        this.listar();
                        break;
                    case 'VALIDATION':
                        this.error_editar=respuesta.data;
                        break;
                    case 'ERROR':
                        break;
                    default:

                        break;
                }
            });
        }
    },
}
</script>