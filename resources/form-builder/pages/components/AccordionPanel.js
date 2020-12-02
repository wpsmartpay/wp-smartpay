import { Card, Accordion } from 'react-bootstrap'
import { MoreHorizontal } from 'react-feather'

export const AccordionPanel = ({ eventKey, title, body }) => {
    return (
        <Card className="mb-3">
            <Card.Header>
                <Accordion.Toggle
                    as="a"
                    variant="link"
                    eventKey={eventKey}
                    className="d-flex justify-content-between align-items-center"
                >
                    <>
                        <h2 className="m-0">{title}</h2>
                        <span>
                            <MoreHorizontal
                                size={18}
                                style={{ marginBottom: '-4px' }}
                            />
                        </span>
                    </>
                </Accordion.Toggle>
            </Card.Header>
            <Accordion.Collapse eventKey={eventKey}>
                <Card.Body>{body}</Card.Body>
            </Accordion.Collapse>
        </Card>
    )
}
