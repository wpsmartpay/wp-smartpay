import { name } from './name'
import { registerBlockType } from '@wordpress/blocks'

const blocks = [name]

export const registerSmartPayFormBlocks = () => {
    blocks.forEach((block) => {
        const { namespace, settings } = block

        registerBlockType(namespace, settings)
    })
}
