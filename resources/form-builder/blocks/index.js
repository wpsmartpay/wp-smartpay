import { name } from './name'
import { email } from './email'
import { payment } from './payment'
import { registerBlockType } from '@wordpress/blocks'

const blocks = [name, email, payment]

export const registerSmartPayFormBlocks = () => {
    blocks.forEach((block) => {
        const { namespace, settings } = block

        registerBlockType(namespace, settings)
    })
}
