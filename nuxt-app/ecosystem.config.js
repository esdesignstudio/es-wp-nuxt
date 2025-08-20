module.exports = {
	apps: [{
        script: './.output/server/index.mjs', // Nuxt3 官網上面說的方式
		instances: 'max', // 負載平衡模式下的 cpu 數量
		exec_mode: 'cluster', // cpu 負載平衡模式
		max_memory_restart: '1G', // 緩存了多少記憶體重新整理
		env_deploy: {
			name: process.env.COMPOSE_PROJECT_NAME || 'client-nuxt-app', // 從環境變數讀取專案名稱
			PORT: process.env.NUXT_PORT || 4001 // 從環境變數讀取端口
		},
	}]
}