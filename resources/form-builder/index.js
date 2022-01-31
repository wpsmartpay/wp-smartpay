import domReady from '@wordpress/dom-ready'
import { render } from '@wordpress/element'
import { HashRouter, Route, Routes } from 'react-router-dom'

import { FormList } from './pages/index'
import { CreateForm } from './pages/create'
import { EditForm } from './pages/edit'

import { NotFound } from '../js/pages/not-found'

import { registerBlocks } from './blocks'

import './styles.scss'
import './store/index'

domReady(function () {
    registerBlocks()

    // registerCoreBlocks()

    const SmartPayForm = () => {
        return (
            <div>
                <HashRouter>
                    <Routes>
                        {/* Form */}
                        <Route exact path="/" element={<FormList />} />
                        <Route exact path="/create" element={<CreateForm />} />
                        <Route
                            exact
                            path="/:formId/edit"
                            element={<EditForm />}
                        />

                        {/* Not Found */}
                        <Route element={<NotFound />} />
                    </Routes>
                </HashRouter>
            </div>
        )
    }

    render(<SmartPayForm />, document.getElementById('smartpay-form'))
})
