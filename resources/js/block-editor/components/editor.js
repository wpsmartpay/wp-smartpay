/**
 * WordPress dependencies
 */
import {
	Popover,
	SlotFillProvider,
	DropZoneProvider,
	FocusReturnProvider,
} from '@wordpress/components';

import { InterfaceSkeleton, FullscreenMode } from "@wordpress/interface";


/**
 * Internal dependencies
 */
import Notices from './notices';
import Header from './header';
import Sidebar from './sidebar';
import BlockEditor from './block-editor';

function Editor( { settings } ) {
	return (
		<>
			<FullscreenMode isActive={false} />
			<SlotFillProvider>
				<DropZoneProvider>
					<FocusReturnProvider>
						<InterfaceSkeleton
							header={<Header />}
							sidebar={<Sidebar />}
							content={
								<>
									<Notices />
									<BlockEditor settings={settings} />
								</>
							}
						/>

						<Popover.Slot />
					</FocusReturnProvider>
				</DropZoneProvider>
			</SlotFillProvider>
		</>
	);
}

export default Editor;