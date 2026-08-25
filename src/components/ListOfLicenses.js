import {useSelect} from '@wordpress/data';
import {useEffect} from "@wordpress/element";

const useBlocks = (deps = [])=> useSelect( select =>{
    const store = select('core/block-editor');
    return store ? store.getBlocks() : [];
}, deps);

/**
 * Replaces lodash isEqual. Everything compared here is a flat list of
 * attachment ids, so an order sensitive element wise comparison is all that is
 * needed - no reason to bundle a deep equality check for it.
 *
 * The identity check comes first so that two undefined sides count as equal,
 * which is what isEqual did: block.dirtyState.imageIds and block.content.imageIds
 * are both undefined until the block state has been initialized, and treating
 * that as a difference would write content on every timeout.
 *
 * Object.is rather than === for the elements, because isEqual compares with
 * SameValueZero and would call two NaN ids equal.
 */
const sameIds = (a, b)=>{
    if(a === b) return true;
    if(!Array.isArray(a) || !Array.isArray(b)) return false;
    return a.length === b.length && a.every((id, i)=> Object.is(id, b[i]));
};

let globalImageIds = [];

const ListOfLicenses = ({block: id})=>{
    const block = window.BlockXComponents.useBlock();
    const blocks = useBlocks();

    const validImageBlocks = blocks.filter(b =>{
        if(b.name !== "core/image") return false;
        if(typeof b.attributes !== typeof [] || typeof b.attributes.id === typeof undefined) return false;
        return true;
    });
    const validGalleryBlocks = blocks.filter(g=>{
       if(g.name !== "core/gallery") return false;
       if(typeof g.attributes !== typeof [] || typeof g.attributes.ids !== typeof []) return false;
       return true;
    });


    globalImageIds = [
        ...new Set([...validImageBlocks.map(b=>b.attributes.id), ...validGalleryBlocks.flatMap(b=>b.attributes.ids)]),
    ];

    useEffect(()=>{
        if( !sameIds(globalImageIds, block.dirtyState.imageIds) ){
            block.changeLocalState("imageIds",globalImageIds);
        }
    }, [globalImageIds]);

    useEffect(()=>{
        const timeout = setTimeout(()=>{
            // wait for change to apply
            if(!sameIds(block.dirtyState.imageIds, block.content.imageIds)){
                block.setContent({
                    imageIds: block.dirtyState.imageIds,
                });
            }
        },300);
        return ()=> clearTimeout(timeout);
    }, [globalImageIds]);

    return <div>
        <window.BlockXComponents.ServerSideRenderQueue
            block={block.blockId}
            attributes={block.attributes}
        />
    </div>
}

export default ListOfLicenses;