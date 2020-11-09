import { name } from './name'
import { registerBlockType } from '@wordpress/blocks'

const blocks = [name]

blocks.forEach((block) => {
	const { namespace, settings } = block

	registerBlockType(namespace, settings)
})
