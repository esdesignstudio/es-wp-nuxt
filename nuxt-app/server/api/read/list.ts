import { promises as fs } from 'fs'
import path from 'path'

export default defineEventHandler(async (event) => {
    const cacheDir = path.join(process.cwd(), 'server/cache')
    try {
        const files = await fs.readdir(cacheDir)
        return { files: files.map((file) => file.replace('.json', '')) }
    } catch (error) {
        return { error: 'Unable to read cache directory' }
    }
})
