<template>
    <v-container fluid>
        <v-card>
            <v-card-title>Avance de Lotes</v-card-title>              
            <v-card-text>
                <v-simple-table
                    class="table-lineal">
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th colspan="4">DETALLES</th>
                                <th colspan="2">INGRESO</th>
                                <th colspan="2">LANZADO</th>
                            </tr>
                            <tr>
                                <th>Codigo</th>
                                <th>Productor</th>
                                <th>Materia</th>
                                <th>Variedad</th>
                                <th>Peso (Kg)</th>
                                <th>N° Jabas</th>
                                <th>Peso (Kg)</th>
                                <th>N° Jabas</th>
                            </tr>   
                        </thead>
                        <tbody>
                            <tr v-for="item in table">
                                <td>{{ item.codigo }}</td>
                                <td>{{ item.descripcion }}</td>
                                <td>{{ item.nombre_materia }}</td>
                                <td>{{ item.nombre_variedad }}</td>
                                <td>{{ item.peso_entrada }}</td>
                                <td>{{ item.num_jabas_entrada }}</td>
                                <td>{{ item.peso_lanzado }}</td>
                                <td>{{ item.num_jabas_lanzadas }}</td>
                            </tr>
                        </tbody>
                    </template> 
                </v-simple-table>
            </v-card-text>
        </v-card>
        <br>
        <v-card>
            <v-card-title>Avance de Personal</v-card-title>              
            <v-card-text>       
                <v-simple-table
                    class="table-lineal">
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th>Linea</th>
                                <th>Cod Labor</th>
                                <th>Labor</th>
                                <th>Cajas Contabilizadas</th>
                            </tr>   
                        </thead>
                        <tbody>
                            <tr v-for="item in table2">
                                <td>{{ item.linea }}</td>
                                <td>{{ item.codigo_labor }}</td>
                                <td>{{ item.descripcion }}</td>
                                <td>{{ item.contador }}</td>
                            </tr>
                        </tbody>
                    </template> 
                </v-simple-table>
            </v-card-text>
        </v-card>
    </v-container>
</template>
<script>
export default {
    data() {
        return {
            table: [],
            table2: [],
            fecha_produccion: moment().format('YYYY-MM-DD'),
        }
    },
    mounted(){
        this.cargar();
    },
    methods:{
        cargar(){
            var t=this;
            t.buscar();
            t.buscar2();
            // setTimeout(() => {
            //     t.cargar();
            // }, 5000);
        },
        buscar(){
            axios.get(`${url_base}/reporte/avance_lote`,{
                params: {
                    fecha_produccion: this.fecha_produccion,
                }
            })
            .then(response => {
                this.table=response.data;
            });
        },
        buscar2(){
            axios.get(`${url_base}/reporte/avance_personal`,{
                params: {
                    fecha_produccion: this.fecha_produccion,
                }
            })
            .then(response => {
                this.table2=response.data;
            });
        }
    }
}
</script>