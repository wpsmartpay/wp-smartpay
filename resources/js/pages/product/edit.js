import { useParams } from 'react-router-dom'

export const EditProduct = () => {
    const { productId } = useParams()

    console.log(productId)
    return <p>Edit product</p>
}
