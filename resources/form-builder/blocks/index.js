import {
    registerBlockType,
    unregisterBlockType,
    setDefaultBlockName,
} from '@wordpress/blocks'
import { registerCoreBlocks } from '@wordpress/block-library'
import { name } from './name'
import { email } from './email'
import { TextField } from './TextField'
import { NumericField } from './NumericField'
import { EmailField } from './EmailField'
import { TextAreaField } from './TextAreaField'
import { RadioField } from './RadioField'
import { AddressField } from './AddressField'

const unregisterBlocks = [
    'core/quote',
    'core/archives',
    'core/audio',
    'core/calendar',
    'core/categories',
    'core/latest-comments',
    'core/latest-posts',
    'core/missing',
    'core/more',
    'core/nextpage',
    'core/preformatted',
    'core/pullquote',
    'core/rss',
    'core/search',
    'core/social-links',
    'core/social-link',
    'core-embed/twitter',
    'core-embed/youtube',
    'core-embed/facebook',
    'core-embed/instagram',
    'core-embed/wordpress',
    'core-embed/soundcloud',
    'core-embed/spotify',
    'core-embed/flickr',
    'core-embed/vimeo',
    'core-embed/animoto',
    'core-embed/cloudup',
    'core-embed/collegehumor',
    'core-embed/crowdsignal',
    'core-embed/dailymotion',
    'core-embed/imgur',
    'core-embed/issuu',
    'core-embed/kickstarter',
    'core-embed/meetup-com',
    'core-embed/mixcloud',
    'core-embed/polldaddy',
    'core-embed/reddit',
    'core-embed/reverbnation',
    'core-embed/screencast',
    'core-embed/scribd',
    'core-embed/slideshare',
    'core-embed/smugmug',
    'core-embed/speaker-deck',
    'core-embed/tiktok',
    'core-embed/ted',
    'core-embed/tumblr',
    'core-embed/videopress',
    'core-embed/wordpress-tv',
    'core-embed/amazon-kindle',
    'core/tag-cloud',
    'core/verse',
    'core/video',
]

const smartPayBlocks = [
    name,
    email,
    TextField,
    NumericField,
    EmailField,
    TextAreaField,
    RadioField,
    AddressField,
]

export const registerBlocks = () => {
    registerCoreBlocks()

    // Remove core blocks
    unregisterBlocks.forEach((name) => {
        unregisterBlockType(name)
    })

    // Register SmartPay blocks
    smartPayBlocks.forEach(({ namespace, settings }) => {
        registerBlockType(namespace, settings)
    })
}
