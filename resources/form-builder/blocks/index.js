import {
    registerBlockType,
    unregisterBlockType,
    setDefaultBlockName,
} from '@wordpress/blocks'
import { registerCoreBlocks } from '@wordpress/block-library'
import { NameField } from './NameField'
import { NameFieldColumn } from './NameField/field'
import { NameLabel } from './NameField/label'
import { NameInput } from './NameField/input'
import { CustomerEmail } from './CustomerEmail'
import { EmailLabel } from './CustomerEmail/label'
import { EmailInput } from './CustomerEmail/input'
import { TextInputField } from './TextInputField'
import { TextInputLabel } from './TextInputField/label'
import { TextInputInput } from './TextInputField/input'
// import { TextField } from './TextField'
// import { NumericField } from './NumericField'
// import { EmailField } from './EmailField'
import { TextAreaField } from './TextAreaField'
import { TextAreaLabel } from './TextAreaField/label'
import { TextAreaInput } from './TextAreaField/input'
import { RadioField } from './RadioField'
import { RadioLabel } from './RadioField/label'
import { RadioInput } from './RadioField/input'
import { AddressField } from './AddressField'
import { AddressFieldLine } from './AddressField/field'
import { AddressLabel } from './AddressField/label'
import { AddressInput } from './AddressField/input'
import { CheckboxField } from './CheckboxField'
import { CheckboxLabel } from './CheckboxField/label'
import { CheckboxInput } from './CheckboxField/input'
import { SelectField } from './SelectField'
import { SelectLabel } from './SelectField/label'
import { SelectInput } from './SelectField/input'
import { PricingField } from './PricingField'
import { PricingOption } from './PricingField/option'
import { SubmitButton } from './SubmitButton'
import { SubmitPay } from './SubmitButton/pay'
import { SubmitCoupon } from './SubmitButton/coupon'
import { GoalProgress } from './GoalProgress'

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
    NameField,
    NameFieldColumn,
    NameLabel,
    NameInput,
    CustomerEmail,
    EmailLabel,
    EmailInput,
    TextInputField,
    TextInputLabel,
    TextInputInput,
    // TextField,
    // NumericField,
    // EmailField,
    TextAreaField,
    TextAreaLabel,
    TextAreaInput,
    RadioField,
    RadioLabel,
    RadioInput,
    AddressField,
    AddressFieldLine,
    AddressLabel,
    AddressInput,
    CheckboxField,
    CheckboxLabel,
    CheckboxInput,
    SelectField,
    SelectLabel,
    SelectInput,
    PricingField,
    PricingOption,
    SubmitButton,
    SubmitPay,
    SubmitCoupon,
    GoalProgress,
]

export const registerBlocks = () => {
    registerCoreBlocks()

    // Remove core blocks
    unregisterBlocks.forEach((name) => {
        // unregisterBlockType(name)
    })

    // Register SmartPay blocks
    smartPayBlocks.forEach(({ namespace, settings }) => {
        registerBlockType(namespace, settings)
    })
}
