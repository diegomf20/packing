<template>
    <v-container fluid>
        <!-- <v-row class=""> -->
        <v-row class="justify-center align-center">
            <v-col
                 cols="12" 
                 lg=6>
                <v-card outlined>
                    <v-card-title>Nueva Operación</v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" lg="6">
                                <v-text-field 
                                    type="number"
                                    outlined
                                    dense
                                    required 
                                    label="Año:" 
                                    v-model="operacion.anio"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6">
                                <v-select
                                    outlined
                                    dense
                                    v-model="operacion.semana"
                                    label="Semana:"
                                    :items="semanas"
                                    item-text="semana"
                                    item-value="semana"
                                    >
                                    </v-select>
                            </v-col>
                            <v-col cols="12" lg="6">
                                <v-text-field 
                                    outlined
                                    dense
                                    required 
                                    label="Descripcion:" 
                                    v-model="operacion.descripcion"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6">
                                <v-text-field 
                                    outlined
                                    dense
                                    type="date"
                                    label="Fecha Operación" 
                                    v-model="operacion.fecha_operacion"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6">
                                <v-text-field 
                                    outlined
                                    dense
                                    type="number"
                                    label="Cantidad de Cajas" 
                                    v-model="operacion.cantidad_cajas"
                                ></v-text-field>
                            </v-col>
                            <v-col cols=12>
                                <v-select
                                    outlined
                                    dense
                                    v-model="operacion.cliente_id"
                                    label="Cliente:"
                                    :items="clientes"
                                    item-text="descripcion"
                                    item-value="id"
                                    >
                                    </v-select>
                            </v-col>
                        </v-row>
                        <div class="text-right mt-3">
                            <v-btn 
                                outlined 
                                color="secondary" 
                                @click="$router.push('/operacion')"
                                >Cancelar</v-btn>
                            <v-btn 
                                outlined 
                                color="primary" 
                                @click="guardar()"
                                >Guardar</v-btn>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
<script>
export default {
    data() {
        return {
            operacion: {
                fecha_operacion: moment().format('YYYY-MM-DD')
            },
            clientes: [],
            semanas: []
        }
    },
    mounted(){
        this.getSemanas();
        this.listarClientes();
        this.operacion.anio=moment().format('Y');
        this.operacion.semana=parseInt(moment().format('w'));
    },
    methods:{
        getSemanas(){
            var semanas=[]
            for (let i = 1; i < 54; i++) {
                semanas.push({semana:i})
            }
            this.semanas=semanas;
        },
        listarClientes(){
            axios.get(url_base+'/cliente?all')
            .then(response => {
                this.clientes=response.data
            });
        },
        guardar(){
            axios.post(`${url_base}/operacion`,this.operacion)
            .then(response => {
                var respuesta=response.data;
                switch (respuesta.status) {
                    case 'OK':
                        swal(respuesta.message, { 
                            icon: "success", 
                            timer: 2000, 
                            buttons: false
                        });
                        this.$router.push(`/operacion`);
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
        }
    }
}
</script>