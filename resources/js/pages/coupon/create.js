import { __ } from '@wordpress/i18n'
import {Container,Form,Tabs,Tab,Row,Col,Button} from 'react-bootstrap'

export const CreateCoupon = ({resturl,nonce}) => {
    const couponCreateHandler = (event) => {
        event.preventDefault();
        fetch(`${resturl}/v1/coupons`,{
            method: 'POST',
            headers: {
                'X-WP-Nonce': nonce
            },
            body: JSON.stringify({title: 'abc'})
        })
        .then(response => response.json())
        .then(data => console.log(data));
    }
    return (
        <>  
            <div className="text-black bg-white border-bottom d-fixed">
                <Container>
                    <div className="d-flex align-items-center justify-content-between">
                        <h2 className="text-black">
                            {__('SmartPay', 'smartpay')}
                        </h2>
                        <div className="ml-auto">
                            <Button
                                type="button"
                                className="btn btn-primary px-3"
                                onClick={couponCreateHandler}
                            >
                                {__('Publish', 'smartpay')}
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>
            <div class="py-5">
                <Container>
                    <Row className="justify-content-center">
                        <Col xs={9}>
                            <Form>
                                <Form.Group controlId="exampleForm.ControlInput3">
                                    <Form.Control type="text" placeholder="Enter coupon code here" />
                                </Form.Group>
                                <Form.Group controlId="exampleForm.ControlInput4">
                                    <Form.Control as="textarea" rows={3} placeholder="Coupon description" />
                                </Form.Group>
                                <div class="py-2">
                                    <Tabs className="mb-3" fill defaultActiveKey="home">
                                        <Tab tabClassName="text-decoration-none" eventKey="home" title="Home">
                                            <Form.Group controlId="exampleForm.SelectCustom">
                                                <Form.Label class="mb-2 d-inline-block">Discount type</Form.Label>
                                                <Form.Control as="select" >
                                                    <option>Fixed Amount</option>
                                                    <option>Percent</option>
                                                </Form.Control>
                                            </Form.Group>
                                            <Form.Group controlId="exampleForm.ControlInput1">
                                                <Form.Label class="mb-2 d-inline-block">Coupon amount</Form.Label>
                                                <Form.Control type="text" placeholder="0" />
                                            </Form.Group>
                                            <Form.Group controlId="exampleForm.ControlInput2">
                                                <Form.Label class="mb-2 d-inline-block">Coupon expiry date</Form.Label>
                                                <Form.Control type="date" />
                                            </Form.Group>
                                        </Tab>
                                        <Tab tabClassName="text-decoration-none" eventKey="usage-restriction" title="Usage Restriction">
                                            <p>Upgrade to pro</p>
                                        </Tab>
                                    </Tabs>
                                </div>
                            </Form>
                        </Col>
                    </Row>
                </Container>
            </div>
        </>
    );
};