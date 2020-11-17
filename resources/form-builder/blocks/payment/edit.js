import {
    Flex,
    FlexBlock,
    FlexItem,
    RadioControl,
    CheckboxControl,
    ToggleControl,
    PanelBody,
    TextControl,
    Button,
} from '@wordpress/components'
import { InspectorControls } from '@wordpress/block-editor'
import { Icon, handle, closeSmall, plus } from '@wordpress/icons'
import { __ } from '@wordpress/i18n'

export const edit = ({ attributes, setAttributes }) => {
    const onShowOptionsChange = (show) => {
        setAttributes({ showOptions: show })
    }

    const onAmountLabelChange = (label, index) => {
        const amounts = [...attributes.amounts]
        amounts[index]['label'] = label
        setAttributes({ amounts })
    }

    const onAmountValueChange = (label, index) => {
        const amounts = [...attributes.amounts]
        amounts[index]['value'] = label
        setAttributes({ amounts })
    }

    const onRemoveAmountField = (index) => {
        const amounts = [...attributes.amounts].filter((amount, i) => {
            return index != i
        })
        setAttributes({ amounts })
    }

    const onAddAmountField = () => {
        setAttributes({
            amounts: [...attributes.amounts, { label: '', value: 0 }],
        })
    }

    // Options
    const onOptionLabelChange = (label, index) => {
        const options = [...attributes.options]
        options[index]['label'] = label
        setAttributes({ options })
    }

    const onOptionValueChange = (label, index) => {
        const options = [...attributes.options]
        options[index]['value'] = label
        setAttributes({ options })
    }

    const onRemoveOptionField = (index) => {
        const options = [...attributes.options].filter((option, i) => {
            return index != i
        })
        setAttributes({ options })
    }

    const onAddOptionField = () => {
        setAttributes({
            options: [...attributes.options, { label: '', value: 0 }],
        })
    }

    return (
        <>
            <div className="form-element">
                <Flex align="start">
                    <FlexBlock>
                        <RadioControl
                            label={__('Amounts', 'smartpay')}
                            selected={attributes.defaultAmount}
                            options={attributes.amounts.map((amount) => {
                                return {
                                    label: `${amount.label} - $${amount.value}`,
                                    value: amount.value,
                                }
                            })}
                            onChange={() => {}}
                        />
                    </FlexBlock>

                    {attributes.showOptions && (
                        <FlexBlock className="components-base-control">
                            <label
                                style={{
                                    display: 'inline-block',
                                    marginBottom: '8px',
                                }}
                                dangerouslySetInnerHTML={{
                                    __html: __('Options', 'smartpay'),
                                }}
                            ></label>
                            {attributes.options.map((option, index) => {
                                return (
                                    <CheckboxControl
                                        key={index}
                                        label={`${option.label} - $${option.value}`}
                                        checked={
                                            option.value ===
                                            attributes.defaultOption
                                        }
                                        value={option.value}
                                        onChange={() => {}}
                                    />
                                )
                            })}
                        </FlexBlock>
                    )}
                </Flex>
                <div style={{ marginTop: '20px' }}>
                    <Button isPrimary>{__('Pay Now', 'smartpay')}</Button>
                </div>
            </div>

            <InspectorControls>
                <PanelBody
                    title={__('Settings', 'smartpay')}
                    initialOpen={true}
                >
                    <ToggleControl
                        label={__('Show options fields', 'smartpay')}
                        checked={attributes.showOptions}
                        value={true}
                        onChange={onShowOptionsChange}
                    />
                </PanelBody>
                <PanelBody title={__('Amounts', 'smartpay')}>
                    {attributes.amounts.map((amount, index) => {
                        return (
                            <Flex key={index}>
                                <FlexItem>
                                    <Icon icon={handle} />
                                </FlexItem>
                                <FlexItem>
                                    <TextControl
                                        type="text"
                                        placeholder="Label"
                                        value={amount.label}
                                        onChange={(label) =>
                                            onAmountLabelChange(label, index)
                                        }
                                    />
                                </FlexItem>
                                <FlexItem>
                                    <TextControl
                                        placeholder="Amount"
                                        value={amount.value}
                                        onChange={(value) =>
                                            onAmountValueChange(value, index)
                                        }
                                    />
                                </FlexItem>
                                <FlexItem>
                                    <Button
                                        icon={closeSmall}
                                        onClick={() =>
                                            onRemoveAmountField(index)
                                        }
                                    />
                                </FlexItem>
                            </Flex>
                        )
                    })}
                    <Button
                        isSecondary
                        style={{
                            marginTop: '20px',
                        }}
                        onClick={() => onAddAmountField()}
                    >
                        <Icon icon={plus} />
                        {__('Add new', 'smartpay')}
                    </Button>
                </PanelBody>

                {attributes.showOptions && (
                    <PanelBody title={__('Options', 'smartpay')}>
                        {attributes.options.map((option, index) => {
                            return (
                                <Flex key={index}>
                                    <FlexItem>
                                        <Icon icon={handle} />
                                    </FlexItem>
                                    <FlexItem>
                                        <TextControl
                                            type="text"
                                            placeholder="Label"
                                            value={option.label}
                                            onChange={(label) =>
                                                onOptionLabelChange(
                                                    label,
                                                    index
                                                )
                                            }
                                        />
                                    </FlexItem>
                                    <FlexItem>
                                        <TextControl
                                            placeholder="option"
                                            value={option.value}
                                            onChange={(value) =>
                                                onOptionValueChange(
                                                    value,
                                                    index
                                                )
                                            }
                                        />
                                    </FlexItem>
                                    <FlexItem>
                                        <Button
                                            icon={closeSmall}
                                            onClick={() =>
                                                onRemoveOptionField(index)
                                            }
                                        />
                                    </FlexItem>
                                </Flex>
                            )
                        })}
                        <Button
                            isSecondary
                            style={{
                                marginTop: '20px',
                            }}
                            onClick={() => onAddOptionField()}
                        >
                            <Icon icon={plus} />
                            {__('Add new', 'smartpay')}
                        </Button>
                    </PanelBody>
                )}
            </InspectorControls>
        </>
    )
}
