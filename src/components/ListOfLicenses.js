import {useSelect} from '@wordpress/data';
import {useEffect} from "@wordpress/element";
import isEqual from 'lodash/isEqual'

const useBlocks = (deps = [])=> useSelect( select =>{
    const store = select('core/block-editor');
    return store ? store.getBlocks() : [];
}, deps);

let globalImageIds = [];

const ListOfLicenses = ({block: id})=>{
    const block = window.BlockXComponents.useBlock();
    const blocks = useBlocks();

    const validImageBlocks = blocks.filter(b =>{
        if(b.name !== "core/image") return false;
        if(typeof b.attributes !== typeof [] || typeof b.attributes.id === typeof undefined) return false;
        return true;
    });
    globalImageIds = [...new Set(validImageBlocks.map(b=>b.attributes.id))];

    useEffect(()=>{
        if( !isEqual(globalImageIds, block.dirtyState.imageIds) ){
            block.changeLocalState("imageIds",globalImageIds);
        }
    }, [globalImageIds]);

    useEffect(()=>{
        const timeout = setTimeout(()=>{
            // wait for change to apply
            if(!isEqual(block.dirtyState.imageIds, block.content.imageIds)){
                block.setContent({
                    imageIds: block.dirtyState.imageIds,
                });
            }
        },300);
        return ()=> clearTimeout(timeout);
    }, [globalImageIds])

    return <div>
        <window.BlockXComponents.ServerSideRenderQueue
            block={block.blockId}
            content={block.content}
        />
    </div>
}

export default ListOfLicenses;