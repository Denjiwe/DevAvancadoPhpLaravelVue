<template>
    <table class="table table-hover">
        <thead>
            <tr>
                <th scope="col" v-for="t, key in titulos" :key="key">{{ t.titulo }}</th>
                <th v-if="atualizar.visivel || visualizar.visivel || remover.visivel">Ações</th>
            </tr>
        </thead>
        <tbody>

            <tr v-for="obj, chave in dadosFiltrados" :key="chave">
                <td v-for="valor, chaveValor in obj" :key="chaveValor">
                    <span v-if="titulos[chaveValor].tipo == 'texto'">{{ valor }}</span>
                    <span v-if="titulos[chaveValor].tipo == 'imagem'">
                        <img :src="'/storage/'+valor" width="30" height="30">
                    </span>
                    <span v-if="titulos[chaveValor].tipo == 'data'">{{ $filters.formataDateTime(valor) }}</span>
                </td>
                <td v-if="atualizar.visivel || visualizar.visivel || remover.visivel">
                    <button class="btn btn-outline-primary btn-sm me-1" v-if="visualizar.visivel" :data-bs-toggle="visualizar.dataBsToggle" :data-bs-target="visualizar.dataBsTarget" @click="setStore(obj)">Visualizar</button>
                    <button class="btn btn-outline-primary btn-sm me-1" v-if="atualizar.visivel" :data-bs-toggle="atualizar.dataBsToggle" :data-bs-target="atualizar.dataBsTarget" @click="setStore(obj)">Atualizar</button>
                    <button class="btn btn-outline-danger btn-sm" v-if="remover" :data-bs-toggle="remover.dataBsToggle" :data-bs-target="remover.dataBsTarget" @click="setStore(obj)">Remover</button>
                </td>
            </tr>

        </tbody>
    </table>
</template>

<script>
    export default {
        props: ['dados', 'titulos', 'visualizar', 'atualizar', 'remover'],
        methods: {
            setStore(obj) {
                this.$store.state.transacao.status = ''
                this.$store.state.transacao.mensagem = ''
                this.$store.state.item = obj
            }
        },  
        computed: {
            dadosFiltrados() {

                let campos = Object.keys(this.titulos)

                let dadosFiltrados = []
                this.dados.map((item, chave) => {

                    let itemFiltrado = {}
                    campos.forEach(campo => {
                        itemFiltrado[campo] = item[campo] //utilizar a sintaxe de array para atribuir valores a objetos
                    })
                    dadosFiltrados.push(itemFiltrado)
                })

                return dadosFiltrados  
            }
        }
    }
</script>