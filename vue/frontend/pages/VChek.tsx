import { FunctionalComponent } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

interface VLinkProps {
  isSuccess: boolean
}

const VCheck: FunctionalComponent<VLinkProps> = props =>
  props.isSuccess ? (
    <FontAwesomeIcon
      icon="check-circle"
      class="text-success"
    />
  ) : (
    <FontAwesomeIcon
      icon="exclamation-circle"
      class="text-danger"
    />
  )

VCheck.props = {
  isSuccess: Boolean
}

export default VCheck
