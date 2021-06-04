const geneateKey = () => Math.random().toString(36).substr(2, 9)

const ONE_TIME = 'One Time'

const defaultAmount = {
    key: geneateKey(),
    label: 'Untitled Label',
    amount: 0,
    billing_type: ONE_TIME,
}

export { geneateKey, ONE_TIME, defaultAmount }
